<?php

namespace spec\Akeneo\PimMigration\Domain\SourcePimDetection;

use Akeneo\PimMigration\Domain\PimConfiguration\ComposerJson;
use Akeneo\PimMigration\Domain\PimConfiguration\PimConfiguration;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePimDetectionException;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Ds\Map;
use PhpSpec\ObjectBehavior;

/**
 * Spec for SourcePimDetector.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(
            'mysql_host',
            3306,
            'database_name',
            'database_user',
            'database_password',
            null,
            null,
            false,
            null,
            false
        );
        $this->shouldHaveType(SourcePim::class);
    }

    public function it_throws_an_exception_if_it_is_not_a_standard(
        ComposerJson $composerJson,
        PimConfiguration $sourcePimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('a-repo');
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromSourcePimConfiguration', [$sourcePimConfiguration]);
        $this->shouldThrow(
            new SourcePimDetectionException(
                'Your PIM name should be either akeneo/pim-community-standard or either akeneo/pim-enterprise-standard, currently a-repo'
            ))->duringInstantiation();
    }

    public function it_throws_an_exception_if_it_is_not_a_one_dot_seven(
        ComposerJson $composerJson,
        PimConfiguration $sourcePimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '~1.6']));
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromSourcePimConfiguration', [$sourcePimConfiguration]);

        $this->shouldThrow(
            new SourcePimDetectionException(
                'Your PIM version should be 1.7.'
            ))->duringInstantiation();
    }
}
