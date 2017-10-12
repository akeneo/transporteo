<?php

namespace spec\Akeneo\PimMigration\Domain\Pim;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimDetectionException;
use Akeneo\PimMigration\Domain\Pim\ComposerJson;
use Akeneo\PimMigration\Domain\Pim\PimApiParameters;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
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
    public function it_is_initializable(PimConnection $connection, PimApiParameters $apiParameters)
    {
        $this->beConstructedWith(
            'mysql',
            3306,
            'akeneo_pim',
            'akeneo_pim',
            'akeneo_pim',
            false,
            '/home/akeneo/pim-destination',
            $connection,
            $apiParameters
        );

        $this->shouldHaveType(DestinationPim::class);
    }

    public function it_throws_an_exception_if_it_is_not_a_standard(
        PimConnection $connection,
        ComposerJson $composerJson,
        PimConfiguration $destinationPimConfiguration,
        PimApiParameters $apiParameters
    ) {
        $composerJson->getRepositoryName()->willReturn('a-repo');
        $destinationPimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromDestinationPimConfiguration', [$connection, $destinationPimConfiguration, $apiParameters]);
        $this->shouldThrow(
            new DestinationPimDetectionException(
                'Your destination PIM name should be either akeneo/pim-community-standard or either akeneo/pim-enterprise-standard, currently a-repo'
            ))->duringInstantiation();
    }

    public function it_throws_an_exception_if_it_is_not_a_two_dot_zero(
        PimConnection $connection,
        ComposerJson $composerJson,
        PimConfiguration $destinationPimConfiguration,
        PimApiParameters $apiParameters
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '~1.6']));
        $destinationPimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromDestinationPimConfiguration', [$connection, $destinationPimConfiguration, $apiParameters]);

        //TODO CORRECT VERSION
        $this->shouldThrow(
            new DestinationPimDetectionException(
                'Your destination PIM version should be 2.0.x currently : ~1.6'
            ))->duringInstantiation();
    }
}
