<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurationException;
use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Ask for the location of the Source Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S010FromReadyToSourcePimConfigured extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    private const LOCAL_SOURCE_PIM = 'locally';
    private const REMOTE_SOURCE_PIM = 'on a remote server';

    private const YES = 'yes';
    private const NO = 'no';

    /** @var SourcePimConfigurator */
    private $pimConfigurator;

    public function __construct(
        Translator $translator,
        LoggerInterface $logger,
        SourcePimConfigurator $pimConfigurator
    ) {
        parent::__construct($translator, $logger);

        $this->pimConfigurator = $pimConfigurator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.leave.ready' => 'leaveReadyPlace',
            'workflow.transporteo.transition.ask_source_pim_location' => 'askSourcePimLocation',
            'workflow.transporteo.guard.local_source_pim_configuration' => 'guardLocalSourcePimConfiguration',
            'workflow.transporteo.guard.distant_source_pim_configuration' => 'guardDistantSourcePimConfiguration',
            'workflow.transporteo.transition.distant_source_pim_configuration' => 'onDistantConfiguration',
            'workflow.transporteo.transition.local_source_pim_configuration' => 'onLocalConfiguration',
        ];
    }

    public function leaveReadyPlace(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        $this->printerAndAsker->title('Transporteo');

        $this
            ->printerAndAsker
            ->printMessage($this->translator->trans('from_ready_to_source_pim_configured.introduction.title'));

        $this
            ->printerAndAsker
            ->note($this->translator->trans('from_ready_to_source_pim_configured.introduction.rules'));

        $this
            ->printerAndAsker
            ->section($this->translator->trans('from_ready_to_source_pim_configured.introduction.start'));

        $this->logExit(__FUNCTION__);
    }

    public function askSourcePimLocation(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $pimLocation = $this->printerAndAsker->askChoiceQuestion(
            $this->translator->trans('from_ready_to_source_pim_configured.ask_source_pim_location.pim_location.question'),
            [self::LOCAL_SOURCE_PIM, self::REMOTE_SOURCE_PIM]
        );
        $stateMachine->setSourcePimLocation($pimLocation);

        $this->logExit(__FUNCTION__);
    }

    public function guardLocalSourcePimConfiguration(GuardEvent $event)
    {
        $this->logGuardEntering(__FUNCTION__);
        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $isBlocked = $pimSourceLocation !== self::LOCAL_SOURCE_PIM;
        $event->setBlocked($isBlocked);

        $this->logGuardResult(__FUNCTION__, $isBlocked);
    }

    public function guardDistantSourcePimConfiguration(GuardEvent $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $isBlocked = $pimSourceLocation !== self::REMOTE_SOURCE_PIM;
        $event->setBlocked($isBlocked);

        $this->logGuardResult(__FUNCTION__, $isBlocked);
    }

    public function onDistantConfiguration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $transPrefix = 'from_ready_to_source_pim_configured.on_distant_configuration.';

        $host = $this->printerAndAsker->askSimpleQuestion(
            $this->translator->trans($transPrefix.'hostname_question'),
            $stateMachine->getDefaultResponse('ssh_hostname_source_pim'),
            function ($answer) use ($transPrefix) {
                if (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $answer)
                    && preg_match('/^.{1,253}$/', $answer)
                    && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $answer)) {
                    return $answer;
                }

                throw new \RuntimeException($this->translator->trans($transPrefix.'hostname_error'));
            }
        );

        $port = (int) $this->printerAndAsker->askSimpleQuestion(
            $this->translator->trans($transPrefix.'ssh_port_question'),
            $stateMachine->getDefaultResponse('ssh_port_source_pim'),
            function ($answer) use ($transPrefix) {
                if (!is_numeric($answer)) {
                    throw new \RuntimeException($this->translator->trans($transPrefix.'ssh_port_error'));
                }

                return $answer;
            }
        );
        $user = $this->printerAndAsker->askSimpleQuestion(
            $this->translator->trans($transPrefix.'ssh_user_question'),
            $stateMachine->getDefaultResponse('ssh_user_source_pim')
        );

        $sshPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                $this->translator->trans($transPrefix.'ssh_key_path_question'),
                $stateMachine->getDefaultResponse('ssh_key_path_source_pim'),
                function ($answer) use ($transPrefix) {
                    $fs = new Filesystem();

                    if (!$fs->isAbsolutePath($answer)) {
                        throw new \RuntimeException($this->translator->trans($transPrefix.'ssh_key_path_error'));
                    }

                    return $answer;
                }
            );

        $hasPassword = $this->printerAndAsker->askChoiceQuestion(
            $this->translator->trans($transPrefix . 'ssh_key_protected'),
            [self::YES => self::YES, self::NO => self::NO]
        );

        $password = null;
        if (self::YES === $hasPassword) {
            $password = $this
                ->printerAndAsker
                ->askHiddenSimpleQuestion(
                    sprintf($this->translator->trans($transPrefix . 'ssh_key_passphrase'), $sshPath)
                );
        }

        $stateMachine->setSourcePimConnection(new SshConnection($host, $port, $user, new SshKey($sshPath), $password));

        $pimProjectPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                $this->translator->trans($transPrefix.'project_path_question'),
                $stateMachine->getDefaultResponse('installation_path_source_pim'),
                function ($answer) use ($transPrefix) {
                    $fs = new Filesystem();

                    if (!$fs->isAbsolutePath($answer)) {
                        throw new \RuntimeException($this->translator->trans($transPrefix.'project_path_error'));
                    }

                    return $answer;
                }
            );

        $composerJsonPath = sprintf(
            '%s%s%s',
            $pimProjectPath,
            DIRECTORY_SEPARATOR === substr($pimProjectPath, -1) ? '' : DIRECTORY_SEPARATOR,
            'composer.json'
        );

        try {
            $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $stateMachine->setSourcePimServerInformation($pimServerInformation);

        try {
            $sourcePimConfiguration = $this->pimConfigurator->configure($stateMachine->getSourcePimConnection(), $pimServerInformation);
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);

        $this->logExit(__FUNCTION__);
    }

    public function onLocalConfiguration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $transPrefix = 'from_ready_to_source_pim_configured.on_local_configuration.';

        $pimProjectPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                $this->translator->trans($transPrefix.'project_path_question'),
                $stateMachine->getDefaultResponse('installation_path_source_pim'),
                function ($answer) use ($transPrefix) {
                    $fs = new Filesystem();

                    if (!$fs->isAbsolutePath($answer)) {
                        throw new \RuntimeException($this->translator->trans($transPrefix.'project_path_error'));
                    }

                    return $answer;
                }
            );

        $stateMachine->setSourcePimConnection(new Localhost());

        $composerJsonPath = sprintf(
            '%s%s%s',
            $pimProjectPath,
            DIRECTORY_SEPARATOR === substr($pimProjectPath, -1) ? '' : DIRECTORY_SEPARATOR,
            'composer.json'
        );

        try {
            $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $stateMachine->setSourcePimServerInformation($pimServerInformation);

        try {
            $sourcePimConfiguration = $this->pimConfigurator->configure($stateMachine->getSourcePimConnection(), $pimServerInformation);
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);

        $this->logExit(__FUNCTION__);
    }
}
