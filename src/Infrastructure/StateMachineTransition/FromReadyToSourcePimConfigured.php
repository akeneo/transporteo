<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\PimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurationException;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\PimConfiguration\PimConfiguratorFactory;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Ask for the location of the Source Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromReadyToSourcePimConfigured extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    private const LOCAL_SOURCE_PIM = 'locally';
    private const REMOTE_SOURCE_PIM = 'on a remote server';

    /** @var FileFetcherFactory */
    private $fileFetcherFactory;

    /** @var PimConfiguratorFactory */
    private $pimConfiguratorFactory;

    public function __construct(
        FileFetcherFactory $fileFfileFetcherFactory,
        PimConfiguratorFactory $sourcePimConfiguratorFactory
    ) {
        $this->fileFetcherFactory = $fileFfileFetcherFactory;
        $this->pimConfiguratorFactory = $sourcePimConfiguratorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.leave.ready'                                 => 'leaveReadyPlace',
            'workflow.migration_tool.transition.ask_source_pim_location'          => 'askSourcePimLocation',
            'workflow.migration_tool.guard.local_source_pim_configuration'        => 'guardLocalSourcePimConfiguration',
            'workflow.migration_tool.guard.distant_source_pim_configuration'      => 'guardDistantSourcePimConfiguration',
            'workflow.migration_tool.transition.distant_source_pim_configuration' => 'onDistantConfiguration',
            'workflow.migration_tool.transition.local_source_pim_configuration'   => 'onLocalConfiguration',
        ];
    }

    public function leaveReadyPlace(Event $event)
    {
        $this->printerAndAsker->printMessage(
            sprintf(
                'This tool aims to help you to migrate your %s (either Community or Enterprise) to the new version 2.0. All your data will be migrated seamlessly. Your source PIM won\'t be updated nor touched. Instead, we\'ll perform the migration in a brand new PIM 2.0.',
                $this->printerAndAsker->getBoldQuestionWords('PIM 1.7 standard edition')
            )
        );
        $this->printerAndAsker->printMessage('In what follows, "source PIM" will refer to your current 1.7 PIM whereas "destination PIM" will refer to your future 2.0 PIM.');
        $this->printerAndAsker->printMessage('Here we are! A few questions before starting to migrate your PIM!');
    }

    public function askSourcePimLocation(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $projectName = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                sprintf(
                    'What is the name of the project you want to migrate? Please choose a name with %s characters. ',
                    $this->printerAndAsker->getBoldQuestionWords('snake_case and alphanumeric')
                ),
                '',
                function ($answer) {
                    if (0 === preg_match('/^[A-Za-z0-9_]+$/', $answer)) {
                        throw new \RuntimeException(
                            'Your project name should use only alphanumeric and snake_case characters.'
                        );
                    }
                }
            );
        $stateMachine->setProjectName($projectName);

        $pimLocation = $this->printerAndAsker->askChoiceQuestion(
            'Where is located your source PIM? ',
            [self::LOCAL_SOURCE_PIM, self::REMOTE_SOURCE_PIM]
        );
        $stateMachine->setSourcePimLocation($pimLocation);
    }

    public function guardLocalSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $event->setBlocked($pimSourceLocation !== self::LOCAL_SOURCE_PIM);
    }

    public function guardDistantSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $event->setBlocked($pimSourceLocation !== self::REMOTE_SOURCE_PIM);
    }

    public function onDistantConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $host = $this->printerAndAsker->askSimpleQuestion(
            'What is the hostname of the source PIM? For instance, myhost.domain.com. Don\'t put the http or https prefix please ;)'
        );
        $port = (int)$this->printerAndAsker->askSimpleQuestion('What is the SSH port of the source PIM? ', '22');
        $user = $this->printerAndAsker->askSimpleQuestion(
            'What is the SSH user you want to connect with to the source PIM? '
        );
        $sshPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                sprintf(
                    'What is the %s path of the %s SSH key able to connect to the source PIM? ',
                    $this->printerAndAsker->getBoldQuestionWords('absolute'),
                    $this->printerAndAsker->getBoldQuestionWords('private')
                )
            );

        $sshKeySourcePimServer = new SshKey($sshPath);
        $stateMachine->setSshKey($sshKeySourcePimServer);
        $serverAccessInformation = new ServerAccessInformation($host, $port, $user, $sshKeySourcePimServer);

        $composerJsonPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                sprintf(
                    'What is the %s path of the source PIM composer.json on the server? ',
                    $this->printerAndAsker->getBoldQuestionWords('absolute')
                )
            );

        try {
            $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $pimConfigurator = $this
            ->pimConfiguratorFactory
            ->createPimConfigurator(
                $this->fileFetcherFactory->createSshFileFetcher($serverAccessInformation)
            );

        try {
            $sourcePimConfiguration = $pimConfigurator->configure($pimServerInformation);
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);
    }

    public function onLocalConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $composerJsonPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                sprintf(
                    'What is the %s path of the source PIM composer.json on your computer? ',
                    $this->printerAndAsker->getBoldQuestionWords('absolute')
                )
            );

        try {
            $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $pimConfigurator = $this
            ->pimConfiguratorFactory
            ->createPimConfigurator(
                $this->fileFetcherFactory->createLocalFileFetcher()
            );

        try {
            $sourcePimConfiguration = $pimConfigurator->configure($pimServerInformation);
        } catch (\Exception $exception) {
            throw new SourcePimConfigurationException($exception->getMessage(), 0, $exception);
        }

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);
    }
}
