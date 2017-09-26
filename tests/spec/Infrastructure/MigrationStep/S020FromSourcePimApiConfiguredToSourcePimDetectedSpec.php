<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\PimMigration\Domain\Pim\ComposerJson;
use Akeneo\PimMigration\Domain\Pim\ParametersYml;
use Akeneo\PimMigration\Domain\Pim\PimApiParameters;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimParameters;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S020FromSourcePimApiConfiguredToSourcePimDetected;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Ds\Map;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * FromSourcePimConfiguredToSourcePimDetectedSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S020FromSourcePimApiConfiguredToSourcePimDetectedSpec extends ObjectBehavior
{
    public function let(Translator $translator, PrinterAndAsker $printerAndAsker)
    {
        $this->beConstructedWith($translator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S020FromSourcePimApiConfiguredToSourcePimDetected::class);
    }

    public function it_can_detect_source_pim(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        PimConfiguration $sourcePimConfiguration,
        PimConnection $sourcePimConnection,
        ComposerJson $composerJson,
        PimParameters $pimParameters,
        ParametersYml $parametersYml,
        PimApiParameters $apiParameters
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $sourcePimRealPath = '/source-pim-real-path';

        $composerJsonPath = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();

        $composerJson->getPath()->willReturn($composerJsonPath);
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => 'v1.7.6']));
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $sourcePimConfiguration->getPimParameters()->willReturn($pimParameters);

        $pimParameters->getCatalogStorageDir()->willReturn('%kernel.root_dir%/file_storage/catalog');
        $pimParameters->getMongoDbDatabase()->willReturn(null);
        $pimParameters->getMongoDbInformation()->willReturn(null);

        $parametersYml->getDatabaseHost()->willReturn('database_host');
        $parametersYml->getDatabaseName()->willReturn('database_name');
        $parametersYml->getDatabasePort()->willReturn(3306);
        $parametersYml->getDatabaseUser()->willReturn('database_user');
        $parametersYml->getDatabasePassword()->willReturn('database_password');
        $parametersYml->getMongoDbDatabase()->willReturn(null);
        $parametersYml->getMongoDbInformation()->willReturn(null);
        $sourcePimConfiguration->getParametersYml()->willReturn($parametersYml);

        $stateMachine->getSourcePimConfiguration()->willReturn($sourcePimConfiguration);
        $stateMachine->getSourcePimRealPath()->willReturn($sourcePimRealPath);
        $stateMachine->getSourcePimConnection()->willReturn($sourcePimConnection);
        $stateMachine->getSourcePimApiParameters()->willReturn($apiParameters);

        $stateMachine->setSourcePim(new SourcePim(
            'database_host',
            3306,
            'database_name',
            'database_user',
            'database_password',
            null,
            null,
            false,
            null,
            false,
            $sourcePimRealPath,
            $sourcePimConnection->getWrappedObject(),
            $apiParameters->getWrappedObject()
        ))->shouldBeCalled();

        $this->onSourcePimDetection($event);
    }

    public function on_source_pim_detected(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(false);
        $sourcePim->getMongoDatabase()->willReturn(null);
        $sourcePim->hasIvb()->willReturn(false);

        $printerAndAsker->printMessage(sprintf(
            'You want to migrate from an edition %s with %s storage%s',
            'Community',
            'ORM',
            '.'
        ))->shouldBeCalled();

        $printerAndAsker->printMessage('Source Pim Detection : Successful')->shouldBeCalled();

        $this->onSourcePimDetected($event);
    }
}
