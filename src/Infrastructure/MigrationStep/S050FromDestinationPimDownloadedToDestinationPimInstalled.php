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
use Akeneo\PimMigration\Domain\Pim\PimApiClientBuilder;
use Akeneo\PimMigration\Domain\Pim\PimApiParameters;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Psr\Log\LoggerInterface;
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

    /** @var PimApiClientBuilder */
    private $apiClientBuilder;

    public function __construct(
        Translator $translator,
        LoggerInterface $logger,
        DestinationPimConfigurator $destinationPimConfigurator,
        DestinationPimSystemRequirementsInstallerHelper $destinationPimSystemRequirementsInstallerHelper,
        DestinationPimConfigurationChecker $destinationPimConfigurationChecker,
        ParametersYmlGenerator $parametersYmlGenerator,
        PimApiClientBuilder $apiClientBuilder
    ) {
        parent::__construct($translator, $logger);

        $this->destinationPimConfigurator = $destinationPimConfigurator;
        $this->destinationPimSystemRequirementsInstallerHelper = $destinationPimSystemRequirementsInstallerHelper;
        $this->destinationPimConfigurationChecker = $destinationPimConfigurationChecker;
        $this->parametersYmlGenerator = $parametersYmlGenerator;
        $this->apiClientBuilder = $apiClientBuilder;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.guard.destination_pim_pre_configuration' => 'guardOnDestinationPimPreConfiguration',
            'workflow.migration_tool.transition.destination_pim_pre_configuration' => 'onDestinationPimPreConfiguration',
            'workflow.migration_tool.guard.destination_pim_configuration' => 'guardOnDestinationPimConfiguration',
            'workflow.migration_tool.transition.destination_pim_configuration' => 'onDestinationPimConfiguration',
            'workflow.migration_tool.transition.destination_pim_api_configuration' => 'onDestinationPimApiConfiguration',
            'workflow.migration_tool.transition.destination_pim_detection' => 'onDestinationPimDetection',
            'workflow.migration_tool.transition.local_destination_pim_system_requirements_installation' => 'onLocalDestinationPimSystemRequirementsInstallation',
            'workflow.migration_tool.transition.destination_pim_check_requirements' => 'onDestinationPimCheckRequirements',
        ];
    }

    public function guardOnDestinationPimPreConfiguration(GuardEvent $event)
    {
        $this->logGuardEntering(__FUNCTION__);

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

        $isBlocking = file_exists($parametersYamlPath);
        $event->setBlocked($isBlocking);

        $this->logGuardResult(__FUNCTION__, $isBlocking);
    }

    public function onDestinationPimPreConfiguration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->parametersYmlGenerator->preconfigure($stateMachine->getCurrentDestinationPimLocation());

        $this->logExit(__FUNCTION__);
    }

    public function guardOnDestinationPimConfiguration(GuardEvent $event)
    {
        $this->logGuardEntering(__FUNCTION__);

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

        $isBlocking = !file_exists($parametersYamlPath);
        $event->setBlocked($isBlocking);

        $this->logGuardResult(__FUNCTION__, $isBlocking);
    }

    public function onDestinationPimConfiguration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $projectName = $stateMachine->getProjectName();
        $composerJsonPath = sprintf(
            '%s%scomposer.json',
            $stateMachine->getCurrentDestinationPimLocation(),
            DIRECTORY_SEPARATOR
        );

        $destinationPimConfiguration = $this->destinationPimConfigurator->configure($stateMachine->getDestinationPimConnection(), new PimServerInformation($composerJsonPath, $projectName));

        $stateMachine->setDestinationPimConfiguration($destinationPimConfiguration);

        $this->logExit(__FUNCTION__);
    }

    public function onDestinationPimApiConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $baseUri = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                $this
                    ->translator
                    ->trans('from_destination_pim_downloaded_to_destination_pim_installed.on_destination_pim_api_configuration.base_uri.question'),
                '',
                function ($answer) {
                    // This URI validation regex is intentionally imperfect.
                    // It's goal is only to avoid common mistakes like forgetting "http", or adding parameters from a copy/paste.
                    if (0 === preg_match('~^https?:\/\/[a-z0-9]+[a-z0-9\-\.]*[a-z0-9]+\/?$~i', $answer)) {
                        throw new \RuntimeException(
                            $this->translator->trans(
                                'from_destination_pim_downloaded_to_destination_pim_installed.on_destination_pim_api_configuration.base_uri.error_message'
                            )
                        );
                    }
                }
            );

        $sourcePimApiParameters = $stateMachine->getSourcePimApiParameters();

        $destinationPimApiParameters = new PimApiParameters(
            $baseUri,
            $sourcePimApiParameters->getClientId(),
            $sourcePimApiParameters->getSecret(),
            $sourcePimApiParameters->getUserName(),
            $sourcePimApiParameters->getUserPwd()
        );

        $stateMachine->setDestinationPimApiParameters($destinationPimApiParameters);
    }

    public function onDestinationPimDetection(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $destinationPim = DestinationPim::fromDestinationPimConfiguration(
                $stateMachine->getDestinationPimConnection(),
                $stateMachine->getDestinationPimConfiguration(),
                $stateMachine->getDestinationPimApiParameters()
            );
        } catch (DestinationPimDetectionException $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $stateMachine->setDestinationPim($destinationPim);

        $this->logExit(__FUNCTION__);
    }

    public function onLocalDestinationPimSystemRequirementsInstallation(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this->destinationPimSystemRequirementsInstallerHelper->install($stateMachine->getDestinationPim());
        } catch (DestinationPimSystemRequirementsNotBootable $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->logExit(__FUNCTION__);
    }

    public function onDestinationPimCheckRequirements(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $this->destinationPimConfigurationChecker->check($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
        } catch (\Exception $exception) {
            throw new DestinationPimInstallationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->logExit(__FUNCTION__);
    }
}
