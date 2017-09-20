<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimConfigurationChecker;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimConfigurator;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstallerHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\ParametersYmlGenerator;
use Akeneo\PimMigration\Domain\Pim\PimApiClientBuilder;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimConfigurationCheckerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimEditionCheckerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimParametersYmlGeneratorFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimSystemRequirementsInstallerFactory;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DestinationPimSystemRequirementsCheckerFactory;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S050FromDestinationPimDownloadedToDestinationPimInstalled;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Installation processus spec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S050FromDestinationPimDownloadedToDestinationPimInstalledSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        DestinationPimConfigurator $destinationPimConfigurator,
        DestinationPimSystemRequirementsInstallerHelper $destinationPimSystemRequirementsInstallerHelper,
        DestinationPimConfigurationChecker $destinationPimConfigurationChecker,
        ParametersYmlGenerator $parametersYmlGenerator,
        PrinterAndAsker $printerAndAsker,
        PimApiClientBuilder $apiClientBuilder
    )
    {
        $this->beConstructedWith(
            $translator,
            $destinationPimConfigurator,
            $destinationPimSystemRequirementsInstallerHelper,
            $destinationPimConfigurationChecker,
            $parametersYmlGenerator,
            $apiClientBuilder
        );

        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S050FromDestinationPimDownloadedToDestinationPimInstalled::class);
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
        $parametersYmlGenerator
    )
    {
        $event->getSubject()->willReturn($migrationToolStateMachine);

        $currentDestinationPimPath = sprintf('%s%sempty_pim_community_standard_2_0',
            ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
            DIRECTORY_SEPARATOR
        );

        $migrationToolStateMachine->getCurrentDestinationPimLocation()->willReturn($currentDestinationPimPath);;

        $parametersYmlGenerator->preconfigure($currentDestinationPimPath)->shouldBeCalled();

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
        PimConnection $pimConnection,
        PimConfiguration $pimConfiguration,
        $destinationPimConfigurator
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
        $stateMachine->getDestinationPimConnection()->willReturn($pimConnection);

        $destinationPimConfigurator->configure($pimConnection, new PimServerInformation(
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

    public function it_configures_the_destination_pim_api(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        AkeneoPimClientInterface $apiClient,
        $printerAndAsker,
        $translator,
        $apiClientBuilder
    )
    {
        $event->getSubject()->willReturn($stateMachine);

        $question = 'What is the base URI to request the API of the destination PIM?';
        $baseUri = 'http://localhost';

        $translator
            ->trans('from_destination_pim_downloaded_to_destination_pim_installed.on_destination_pim_api_configuration.base_uri.question')
            ->willReturn($question);

        $printerAndAsker
            ->askSimpleQuestion($question, '', Argument::any())
            ->willReturn($baseUri);

        $stateMachine->getApiClientId()->WillReturn('clientId');
        $stateMachine->getApiSecret()->WillReturn('secret');
        $stateMachine->getApiUserName()->WillReturn('userName');
        $stateMachine->getApiUserPwd()->WillReturn('userPwd');

        $apiClientBuilder->buildAuthenticatedByPassword(
            $baseUri,
            'clientId',
            'secret',
            'userName',
            'userPwd'
        )->willReturn($apiClient);

        $stateMachine->setDestinationPimApiClient($apiClient)->shouldBeCalled();

        $this->onDestinationPimApiConfiguration($event);
    }
}
