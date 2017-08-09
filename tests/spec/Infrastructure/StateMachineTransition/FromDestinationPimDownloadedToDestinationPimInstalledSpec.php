<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\ParametersYmlGenerator;
use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\PimConfiguration\PimConfiguration;
use Akeneo\PimMigration\Domain\PimConfiguration\PimConfigurator;
use Akeneo\PimMigration\Domain\PimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimConfigurationCheckerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimParametersYmlGeneratorFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimSystemRequirementsInstallerFactory;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\PimConfiguration\PimConfiguratorFactory;
use Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromDestinationPimDownloadedToDestinationPimInstalled;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Installation processus spec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimDownloadedToDestinationPimInstalledSpec extends ObjectBehavior
{
    public function let(
        DestinationPimParametersYmlGeneratorFactory $destinationPimPreConfiguratorFactory,
        PimConfiguratorFactory $pimConfiguratorFactory,
        FileFetcherFactory $fileFetcherFactory,
        DestinationPimSystemRequirementsInstallerFactory $destinationPimSystemRequirementsInstallerFactory,
        CommandLauncherFactory $commandLauncherFactory,
        DestinationPimConfigurationCheckerFactory $destinationPimConfigurationCheckerFactory,
        PrinterAndAsker $printerAndAsker
    )
    {
        $this->beConstructedWith(
            $destinationPimPreConfiguratorFactory,
            $pimConfiguratorFactory,
            $fileFetcherFactory,
            $destinationPimSystemRequirementsInstallerFactory,
            $commandLauncherFactory,
            $destinationPimConfigurationCheckerFactory
        );

        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FromDestinationPimDownloadedToDestinationPimInstalled::class);
    }

    public function it_allows_the_pre_configuration_when_the_parameters_file_does_not_exist(
        MigrationToolStateMachine $migrationToolStateMachine,
        GuardEvent $guardEvent
    )
    {
        $guardEvent->getSubject()->willReturn($migrationToolStateMachine);
        $migrationToolStateMachine->getCurrentDestinationPimLocation()->willReturn(
            sprintf('%s%spim_community_standard_2_0',
                ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
                DIRECTORY_SEPARATOR
            )
        );
        $guardEvent->setBlocked(false)->shouldBeCalled();

        $this->guardOnDestinationPimPreConfiguration($guardEvent);
    }

    public function it_blocks_the_pre_configuration_when_the_parameters_file_exists(
        MigrationToolStateMachine $migrationToolStateMachine,
        GuardEvent $guardEvent
    )
    {
        $guardEvent->getSubject()->willReturn($migrationToolStateMachine);
        $migrationToolStateMachine->getCurrentDestinationPimLocation()->willReturn(
            sprintf('%s%sempty_pim_community_standard_2_0',
                ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
                DIRECTORY_SEPARATOR
            )
        );
        $guardEvent->setBlocked(true)->shouldBeCalled();

        $this->guardOnDestinationPimPreConfiguration($guardEvent);
    }

    public function it_preconfigures_the_destination_pim(
        Event $event,
        MigrationToolStateMachine $migrationToolStateMachine,
        ParametersYmlGenerator $destinationPimPreConfigurator,
        $destinationPimPreConfiguratorFactory
    )
    {
        $event->getSubject()->willReturn($migrationToolStateMachine);

        $currentDestinationPimPath = sprintf('%s%sempty_pim_community_standard_2_0',
            ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
            DIRECTORY_SEPARATOR
        );

        $migrationToolStateMachine->getCurrentDestinationPimLocation()->willReturn($currentDestinationPimPath);

        $destinationPimPreConfiguratorFactory
            ->createDestinationPimParametersYmlGenerator($currentDestinationPimPath)
            ->willReturn($destinationPimPreConfigurator);

        $destinationPimPreConfigurator->preconfigure()->shouldBeCalled();

        $this->onDestinationPimPreConfiguration($event);
    }

    public function it_blocks_the_configuration_if_parameters_file_does_not_exist(
        GuardEvent $guardEvent,
        MigrationToolStateMachine $migrationToolStateMachine
    )
    {
        $guardEvent->getSubject()->willReturn($migrationToolStateMachine);
        $migrationToolStateMachine->getCurrentDestinationPimLocation()->willReturn(
            sprintf('%s%pim_community_standard_2_0',
                ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
                DIRECTORY_SEPARATOR
            )
        );
        $guardEvent->setBlocked(true)->shouldBeCalled();

        $this->guardOnDestinationPimConfiguration($guardEvent);
    }

    public function it_allows_the_configuration_if_parameters_file_exists(
        GuardEvent $guardEvent,
        MigrationToolStateMachine $migrationToolStateMachine
    )
    {
        $guardEvent->getSubject()->willReturn($migrationToolStateMachine);
        $migrationToolStateMachine->getCurrentDestinationPimLocation()->willReturn(
            sprintf('%s%sempty_pim_community_standard_2_0',
                ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
                DIRECTORY_SEPARATOR
            )
        );
        $guardEvent->setBlocked(false)->shouldBeCalled();

        $this->guardOnDestinationPimConfiguration($guardEvent);
    }

    public function it_configures_the_destination_pim(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        PimConfigurator $pimConfigurator,
        FileFetcher $fileFetcher,
        PimConfiguration $pimConfiguration,
        $pimConfiguratorFactory,
        $fileFetcherFactory
    )
    {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getProjectName()->willReturn('a-project');

        $currentDestinationPimLocation = sprintf(
            '%s%spim_community_standard_2_0',
            ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
            DIRECTORY_SEPARATOR
        );

        $stateMachine->getCurrentDestinationPimLocation()->willReturn($currentDestinationPimLocation);

        $fileFetcherFactory->createWithoutCopyLocalFileFetcher()->willReturn($fileFetcher);
        $pimConfiguratorFactory->createPimConfigurator($fileFetcher)->willReturn($pimConfigurator);
        $pimConfigurator->configure(new PimServerInformation(
            sprintf(
                '%s%scomposer.json',
                $currentDestinationPimLocation,
                DIRECTORY_SEPARATOR
            ),
            'a-project'
        ))->willReturn($pimConfiguration);

        $stateMachine->setDestinationPimConfiguration($pimConfiguration)->shouldBeCalled();

        $this->onDestinationPimConfiguration($event);
    }
}