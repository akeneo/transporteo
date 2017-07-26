<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SourcePimConfiguration\SourcePimConfiguratorFactory;
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
    /** @var FileFetcherFactory */
    private $fileFetcherFactory;

    /** @var SourcePimConfiguratorFactory */
    private $sourcePimConfiguratorFactory;

    public function __construct(FileFetcherFactory $fileFfileFetcherFactory, SourcePimConfiguratorFactory $sourcePimConfiguratorFactory)
    {
        $this->fileFetcherFactory = $fileFfileFetcherFactory;
        $this->sourcePimConfiguratorFactory = $sourcePimConfiguratorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.leave.ready' => 'leaveReadyPlace',
            'workflow.migration_tool.transition.ask_source_pim_location' => 'askSourcePimLocation',
            'workflow.migration_tool.guard.local_source_pim_configuration' => 'guardLocalSourcePimConfiguration',
            'workflow.migration_tool.guard.distant_source_pim_configuration' => 'guardDistantSourcePimConfiguration',
            'workflow.migration_tool.transition.distant_source_pim_configuration' => 'onDistantConfiguration',
            'workflow.migration_tool.transition.local_source_pim_configuration' => 'onLocalConfiguration',
        ];
    }

    public function leaveReadyPlace(Event $event)
    {
        $this->printerAndAsker->printMessage('Here you are ! Few questions before start to migrate the PIM !');
    }

    public function askSourcePimLocation(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $projectName = $this->printerAndAsker->askSimpleQuestion('What is the name of the project you want to migrate? ');
        $stateMachine->setProjectName($projectName);

        $pimLocation = $this->printerAndAsker->askChoiceQuestion('Where is located your PIM? ', ['local', 'server']);
        $stateMachine->setSourcePimLocation($pimLocation);
    }

    public function guardLocalSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $event->setBlocked($pimSourceLocation !== 'local');
    }

    public function guardDistantSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $event->setBlocked($pimSourceLocation !== 'server');
    }

    public function onDistantConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage('Source Pim Configuration: Collect your configuration files from a server');

        $host = $this->printerAndAsker->askSimpleQuestion('What is the hostname of the source PIM server? ');
        $port = (int) $this->printerAndAsker->askSimpleQuestion('What is the SSH port of the source PIM server? ', '22');
        $user = $this->printerAndAsker->askSimpleQuestion('What is the SSH user you want to connect with ? ');
        $sshPath = $this->printerAndAsker->askSimpleQuestion('Where is located the private SSH key able to connect to the server? ');

        $sshKeySourcePimServer = new SshKey($sshPath);
        $stateMachine->setSshKey($sshKeySourcePimServer);
        $serverAccessInformation = new ServerAccessInformation($host, $port, $user, $sshKeySourcePimServer);


        $composerJsonPath = $this->printerAndAsker->askSimpleQuestion('Where is located the composer.json on the server? ');
        $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());

        $sourcePimConfigurator = $this
            ->sourcePimConfiguratorFactory
            ->createSourcePimConfigurator(
                $this->fileFetcherFactory->createSshFileFetcher($serverAccessInformation)
            );

        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);
    }

    public function onLocalConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage('Source Pim Configuration: Collect your configuration files from your computer');

        $composerJsonPath = $this->printerAndAsker->askSimpleQuestion('Where is located the composer.json on your computer? ');

        $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());

        $sourcePimConfigurator = $this
            ->sourcePimConfiguratorFactory
            ->createSourcePimConfigurator(
                $this->fileFetcherFactory->createLocalFileFetcher()
            )
        ;
        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);
    }
}
