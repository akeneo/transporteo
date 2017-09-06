<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimConfigurationChecker;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimConfigurator;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimDetectionException;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimInstallationException;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstallerHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\ParametersYmlGenerator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsNotBootable;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Install new PIM step.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S050FromDestinationPimDownloadedToDestinationPimInstalled extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var DestinationPimConfigurator */
    private $destinationPimConfigurator;

    /** @var DestinationPimSystemRequirementsInstallerHelper */
    private $destinationPimSystemRequirementsInstallerHelper;

    /** @var DestinationPimConfigurationChecker */
    private $destinationPimConfigurationChecker;

    /** @var ParametersYmlGenerator */
    private $parametersYmlGenerator;

    public function __construct(
        Translator $translator,
        DestinationPimConfigurator $destinationPimConfigurator,
        DestinationPimSystemRequirementsInstallerHelper $destinationPimSystemRequirementsInstallerHelper,
        DestinationPimConfigurationChecker $destinationPimConfigurationChecker,
        ParametersYmlGenerator $parametersYmlGenerator
    ) {
        parent::__construct($translator);

        $this->destinationPimConfigurator = $destinationPimConfigurator;
        $this->destinationPimSystemRequirementsInstallerHelper = $destinationPimSystemRequirementsInstallerHelper;
        $this->destinationPimConfigurationChecker = $destinationPimConfigurationChecker;
        $this->parametersYmlGenerator = $parametersYmlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.guard.destination_pim_pre_configuration' => 'guardOnDestinationPimPreConfiguration',
            'workflow.migration_tool.transition.destination_pim_pre_configuration' => 'onDestinationPimPreConfiguration',
            'workflow.migration_tool.guard.destination_pim_configuration' => 'guardOnDestinationPimConfiguration',
            'workflow.migration_tool.transition.destination_pim_configuration' => 'onDestinationPimConfiguration',
            'workflow.migration_tool.transition.destination_pim_detection' => 'onDestinationPimDetection',
            'workflow.migration_tool.guard.docker_destination_pim_system_requirements_installation' => 'guardOnDockerDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.transition.docker_destination_pim_system_requirements_installation' => 'onDockerDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.guard.local_destination_pim_system_requirements_installation' => 'guardOnLocalDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.transition.local_destination_pim_system_requirements_installation' => 'onLocalDestinationPimSystemRequirementsInstallation',
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

        $this->parametersYmlGenerator->preconfigure($stateMachine->getCurrentDestinationPimLocation());
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

        $destinationPimConfiguration = $this->destinationPimConfigurator->configure(new PimServerInformation($composerJsonPath, $projectName));

        $stateMachine->setDestinationPimConfiguration($destinationPimConfiguration);
    }

    public function onDestinationPimDetection(Event $event)
    {
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
        $this->printerAndAsker->printMessage('Docker is currently installing the destination PIM... Please wait...');

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this->destinationPimSystemRequirementsInstallerHelper->install($stateMachine->getDestinationPim());
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
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this->destinationPimSystemRequirementsInstallerHelper->install($stateMachine->getDestinationPim());
        } catch (DestinationPimSystemRequirementsNotBootable $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function onDestinationPimCheckRequirements(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this->destinationPimConfigurationChecker->check($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
        } catch (\Exception $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
