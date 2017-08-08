<?php

namespace spec\Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimDetectionException;
use Akeneo\PimMigration\Domain\PimConfiguration\ComposerJson;
use Akeneo\PimMigration\Domain\PimConfiguration\ParametersYml;
use Akeneo\PimMigration\Domain\PimConfiguration\PimConfiguration;
use Ds\Map;
use PhpSpec\ObjectBehavior;

/**
 * Spec for SourcePimDetector.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(
            'mysql',
            3306,
            'akeneo_pim',
            'akeneo_pim',
            'akeneo_pim',
            false,
            null,
            'akeneo_pim',
            '\'elasticsearch: 9200\'',
            '/home/akeneo/pim-destination'
        );

        $this->shouldHaveType(DestinationPim::class);
    }

    public function it_throws_an_exception_if_it_is_not_a_standard(
        ComposerJson $composerJson,
        PimConfiguration $destinationPimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('a-repo');
        $destinationPimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromDestinationPimConfiguration', [$destinationPimConfiguration]);
        $this->shouldThrow(
            new DestinationPimDetectionException(
                'Your destination PIM name should be either akeneo/pim-community-standard or either akeneo/pim-enterprise-standard, currently a-repo'
            ))->duringInstantiation();
    }

    public function it_throws_an_exception_if_it_is_not_a_two_dot_zero(
        ComposerJson $composerJson,
        PimConfiguration $destinationPimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '~1.6']));
        $destinationPimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromDestinationPimConfiguration', [$destinationPimConfiguration]);

        //TODO CORRECT VERSION
        $this->shouldThrow(
            new DestinationPimDetectionException(
                'Your destination PIM version should be 1.8.x-dev@dev currently : ~1.6'
            ))->duringInstantiation();
    }

    public function it_throws_an_exception_when_elasticsearch_hosts_config_is_not_filled(
        ComposerJson $composerJson,
        ParametersYml $parametersYml,
        PimConfiguration $destinationPimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '1.8.x-dev@dev']));
        $destinationPimConfiguration->getComposerJson()->willReturn($composerJson);

        $parametersYml->getDatabaseHost()->willReturn('mysql');
        $parametersYml->getDatabasePort()->willReturn(3306);
        $parametersYml->getDatabaseUser()->willReturn('akeneo_pim');
        $parametersYml->getDatabasePassword()->willReturn('akeneo_pim');
        $parametersYml->getDatabaseName()->willReturn('akeneo_pim');
        $parametersYml->getIndexHosts()->willReturn(null);
        $destinationPimConfiguration->getParametersYml()->willReturn($parametersYml);

        $this->beConstructedThrough('fromDestinationPimConfiguration', [$destinationPimConfiguration]);

        $this->shouldThrow(
            new DestinationPimDetectionException(
                'Your configuration should have an index_hosts key in your parameters.yml file'
            ))->duringInstantiation();
    }

    public function it_throws_an_exception_when_elasticsearch_index_name_config_is_not_filled(
        ComposerJson $composerJson,
        ParametersYml $parametersYml,
        PimConfiguration $destinationPimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '1.8.x-dev@dev']));
        $destinationPimConfiguration->getComposerJson()->willReturn($composerJson);

        $parametersYml->getDatabaseHost()->willReturn('mysql');
        $parametersYml->getDatabasePort()->willReturn(3306);
        $parametersYml->getDatabaseUser()->willReturn('akeneo_pim');
        $parametersYml->getDatabasePassword()->willReturn('akeneo_pim');
        $parametersYml->getDatabaseName()->willReturn('akeneo_pim');
        $parametersYml->getIndexHosts()->willReturn('\'elasticsearch: 9200\'');
        $parametersYml->getIndexName()->willReturn(null);
        $destinationPimConfiguration->getParametersYml()->willReturn($parametersYml);

        $this->beConstructedThrough('fromDestinationPimConfiguration', [$destinationPimConfiguration]);

        $this->shouldThrow(
            new DestinationPimDetectionException(
                'Your configuration should have an index_name key in your parameters.yml file'
            ))->duringInstantiation();
    }
}
