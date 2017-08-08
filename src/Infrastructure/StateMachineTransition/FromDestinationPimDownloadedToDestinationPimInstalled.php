<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimDetectionException;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimInstallationException;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemNotBootable;
use Akeneo\PimMigration\Domain\PimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimConfigurationCheckerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimParametersYmlGeneratorFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimSystemRequirementsInstallerFactory;
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

    /** @var CommandLauncherFactory */
    private $commandLauncherFactory;

    /** @var DestinationPimConfigurationCheckerFactory */
    private $destinationPimConfigurationCheckerFactory;

    public function __construct(
        DestinationPimParametersYmlGeneratorFactory $destinationPimPreConfiguratorFactory,
        PimConfiguratorFactory $pimConfiguratorFactory,
        FileFetcherFactory $fileFetcherFactory,
        DestinationPimSystemRequirementsInstallerFactory $destinationPimSystemRequirementsInstallerFactory,
        CommandLauncherFactory $commandLauncherFactory,
        DestinationPimConfigurationCheckerFactory $destinationPimConfigurationCheckerFactory
    ) {
        $this->destinationPimParametersYmlGeneratorFactory = $destinationPimPreConfiguratorFactory;
        $this->pimConfiguratorFactory = $pimConfiguratorFactory;
        $this->fileFetcherFactory = $fileFetcherFactory;
        $this->destinationPimSystemRequirementsInstallerFactory = $destinationPimSystemRequirementsInstallerFactory;
        $this->commandLauncherFactory = $commandLauncherFactory;
        $this->destinationPimConfigurationCheckerFactory = $destinationPimConfigurationCheckerFactory;
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
            'workflow.migration_tool.guard.destination_pim_system_requirements_installation' => 'guardOnDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.transition.destination_pim_system_requirements_installation' => 'onDestinationPimSystemRequirementsInstallation',
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
        $this->printerAndAsker->printMessage('Destination Pim Configuration : Configure your future PIM');
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

    public function guardOnDestinationPimSystemRequirementsInstallation(GuardEvent $guardEvent)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $guardEvent->getSubject();

        $guardEvent->setBlocked(false === $stateMachine->useDocker());
    }

    public function onDestinationPimSystemRequirementsInstallation(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Installation : Let docker makes the job');

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this
                ->destinationPimSystemRequirementsInstallerFactory
                ->createDockerPimSystemRequirementsInstaller($this->commandLauncherFactory->createDockerComposeCommandLauncher('fpm'))
                ->install($stateMachine->getDestinationPim())
            ;
        } catch (DestinationPimSystemNotBootable $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function guardOnDestinationPimCheckRequirements(GuardEvent $guardEvent)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $guardEvent->getSubject();

        $guardEvent->setBlocked(true === $stateMachine->useDocker() && false === $guardEvent->getMarking()->has('destination_pim_system_requirements_installed'));
    }

    public function onDestinationPimCheckRequirements(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage('Destination Pim : Check Requirements');

        try {
            $this
                ->destinationPimConfigurationCheckerFactory
                ->createDestinationPimConfigurationChecker(
                    $stateMachine->useDocker() ? $this->commandLauncherFactory->createDockerComposeCommandLauncher('fpm') : $this->commandLauncherFactory->createBasicCommandLauncher()
                )
                ->check($stateMachine->getSourcePim(), $stateMachine->getDestinationPim())
            ;
        } catch (\Exception $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
