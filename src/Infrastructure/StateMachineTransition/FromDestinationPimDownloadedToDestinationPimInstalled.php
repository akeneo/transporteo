<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimDetectionException;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimEditionCheckerFactory;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimInstallationException;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsNotBootable;
use Akeneo\PimMigration\Domain\PimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Infrastructure\Command\DestinationPimCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimConfigurationCheckerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimParametersYmlGeneratorFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimSystemRequirementsInstallerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimSystemRequirementsCheckerFactory;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\PimConfiguration\PimConfiguratorFactory;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Install new PIM step.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimDownloadedToDestinationPimInstalled extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var DestinationPimParametersYmlGeneratorFactory */
    private $destinationPimParametersYmlGeneratorFactory;

    /** @var PimConfiguratorFactory */
    private $pimConfiguratorFactory;

    /** @var FileFetcherFactory */
    private $fileFetcherFactory;

    /** @var DestinationPimSystemRequirementsInstallerFactory */
    private $destinationPimSystemRequirementsInstallerFactory;

    /** @var DestinationPimCommandLauncherFactory */
    private $commandLauncherFactory;

    /** @var DestinationPimConfigurationCheckerFactory */
    private $destinationPimConfigurationCheckerFactory;

    /** @var DestinationPimEditionCheckerFactory */
    private $destinationPimEditionCheckerFactory;

    /** @var DestinationPimSystemRequirementsCheckerFactory */
    private $destinationPimSystemRequirementsCheckerFactory;

    public function __construct(
        DestinationPimParametersYmlGeneratorFactory $destinationPimPreConfiguratorFactory,
        PimConfiguratorFactory $pimConfiguratorFactory,
        FileFetcherFactory $fileFetcherFactory,
        DestinationPimSystemRequirementsInstallerFactory $destinationPimSystemRequirementsInstallerFactory,
        DestinationPimCommandLauncherFactory $commandLauncherFactory,
        DestinationPimConfigurationCheckerFactory $destinationPimConfigurationCheckerFactory,
        DestinationPimEditionCheckerFactory $destinationPimEditionCheckerFactory,
        DestinationPimSystemRequirementsCheckerFactory $destinationPimSystemRequirementsCheckerFactory
    ) {
        $this->destinationPimParametersYmlGeneratorFactory = $destinationPimPreConfiguratorFactory;
        $this->pimConfiguratorFactory = $pimConfiguratorFactory;
        $this->fileFetcherFactory = $fileFetcherFactory;
        $this->destinationPimSystemRequirementsInstallerFactory = $destinationPimSystemRequirementsInstallerFactory;
        $this->commandLauncherFactory = $commandLauncherFactory;
        $this->destinationPimConfigurationCheckerFactory = $destinationPimConfigurationCheckerFactory;
        $this->destinationPimEditionCheckerFactory = $destinationPimEditionCheckerFactory;
        $this->destinationPimSystemRequirementsCheckerFactory = $destinationPimSystemRequirementsCheckerFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.guard.destination_pim_pre_configuration' => 'guardOnDestinationPimPreConfiguration',
            'workflow.migration_tool.transition.destination_pim_pre_configuration' => 'onDestinationPimPreConfiguration',
            'workflow.migration_tool.guard.destination_pim_configuration' => 'guardOnDestinationPimConfiguration',
            'workflow.migration_tool.announce.destination_pim_configuration' => 'onDestinationPimConfigurationAvailable',
            'workflow.migration_tool.transition.destination_pim_configuration' => 'onDestinationPimConfiguration',
            'workflow.migration_tool.transition.destination_pim_detection' => 'onDestinationPimDetection',
            'workflow.migration_tool.guard.docker_destination_pim_system_requirements_installation' => 'guardOnDockerDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.transition.docker_destination_pim_system_requirements_installation' => 'onDockerDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.guard.local_destination_pim_system_requirements_installation' => 'guardOnLocalDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.transition.local_destination_pim_system_requirements_installation' => 'onLocalDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.guard.destination_pim_check_requirements' => 'guardOnDestinationPimCheckRequirements',
            'workflow.migration_tool.transition.destination_pim_check_requirements' => 'onDestinationPimCheckRequirements',
        ];
    }

    public function guardOnDestinationPimPreConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $destinationPimPath = $stateMachine->getCurrentDestinationPimLocation();
        $parametersYamlPath = sprintf(
            '%s%sapp%sconfig%sparameters.yml',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $event->setBlocked(file_exists($parametersYamlPath));
    }

    public function onDestinationPimPreConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $preConfigurator = $this->destinationPimParametersYmlGeneratorFactory->createDestinationPimParametersYmlGenerator($stateMachine->getCurrentDestinationPimLocation());

        $preConfigurator->preconfigure();
    }

    public function onDestinationPimConfigurationAvailable(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Configuration : Configure your destination PIM');
    }

    public function guardOnDestinationPimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $destinationPimPath = $stateMachine->getCurrentDestinationPimLocation();
        $parametersYamlPath = sprintf(
            '%s%sapp%sconfig%sparameters.yml',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $event->setBlocked(!file_exists($parametersYamlPath));
    }

    public function onDestinationPimConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $projectName = $stateMachine->getProjectName();
        $composerJsonPath = sprintf(
            '%s%scomposer.json',
            $stateMachine->getCurrentDestinationPimLocation(),
            DIRECTORY_SEPARATOR
        );

        $pimConfigurator = $this->pimConfiguratorFactory->createPimConfigurator($this->fileFetcherFactory->createWithoutCopyLocalFileFetcher());

        $destinationPimConfiguration = $pimConfigurator->configure(new PimServerInformation($composerJsonPath, $projectName));

        $stateMachine->setDestinationPimConfiguration($destinationPimConfiguration);
    }

    public function onDestinationPimDetection(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Detection : Detect your future PIM.');

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $destinationPim = DestinationPim::fromDestinationPimConfiguration($stateMachine->getDestinationPimConfiguration());
        } catch (DestinationPimDetectionException $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $stateMachine->setDestinationPim($destinationPim);
    }

    public function guardOnDockerDestinationPimSystemRequirementsInstallation(GuardEvent $guardEvent)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $guardEvent->getSubject();

        $guardEvent->setBlocked(false === $stateMachine->useDocker());
    }

    public function onDockerDestinationPimSystemRequirementsInstallation(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Installation : Let docker makes the job');

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this
                ->destinationPimSystemRequirementsInstallerFactory
                ->createDockerPimSystemRequirementsInstaller(
                    $this->commandLauncherFactory->createDockerComposeCommandLauncher('fpm'),
                    $this->commandLauncherFactory->createBasicDestinationPimCommandLauncher()
                )
                ->install($stateMachine->getDestinationPim())
            ;
        } catch (DestinationPimSystemRequirementsNotBootable $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function guardOnLocalDestinationPimSystemRequirementsInstallation(GuardEvent $guardEvent)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $guardEvent->getSubject();

        $guardEvent->setBlocked(true === $stateMachine->useDocker());
    }

    public function onLocalDestinationPimSystemRequirementsInstallation(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Installation : Prepare your local environment');

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this
                ->destinationPimSystemRequirementsInstallerFactory
                ->createBasicPimSystemRequirementsInstaller($this->commandLauncherFactory->createBasicDestinationPimCommandLauncher())
                ->install($stateMachine->getDestinationPim())
            ;
        } catch (DestinationPimSystemRequirementsNotBootable $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function onDestinationPimCheckRequirements(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage('Destination Pim : Check Requirements');

        $commandLauncher = $stateMachine->useDocker() ? $this->commandLauncherFactory->createDockerComposeCommandLauncher('fpm') : $this->commandLauncherFactory->createBasicDestinationPimCommandLauncher();
        $editionChecker = $this->destinationPimEditionCheckerFactory->createDestinationPimEditionChecker();
        $systemRequirementschecker = $this->destinationPimSystemRequirementsCheckerFactory->createCliDestinationPimSystemRequirementsChecker($commandLauncher);

        $pimConfigurationChecker = $this->destinationPimConfigurationCheckerFactory->createDestinationPimConfigurationChecker($editionChecker, $systemRequirementschecker);

        try {
            $pimConfigurationChecker->check($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
        } catch (\Exception $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->printerAndAsker->printMessage('Destination Pim : Ready');
    }
}
