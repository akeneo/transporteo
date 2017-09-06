<?php

namespace spec\Akeneo\PimMigration\Domain\Pim;

use Akeneo\PimMigration\Domain\Pim\ComposerJson;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\MigrationStep\s020_SourcePimDetection\SourcePimDetectionException;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
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
    public function it_is_initializable(PimConnection $connection)
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
            false,
            '/a-path',
            $connection
        );
        $this->shouldHaveType(SourcePim::class);
    }

    public function it_throws_an_exception_if_it_is_not_a_standard(
        PimConnection $connection,
        ComposerJson $composerJson,
        PimConfiguration $sourcePimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('a-repo');
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromSourcePimConfiguration', [$connection, '/source-pim-real-path', $sourcePimConfiguration]);
        $this->shouldThrow(
            new SourcePimDetectionException(
                'Your PIM distribution should be either "akeneo/pim-community-standard" or "akeneo/pim-enterprise-standard". It appears you try to migrate a "a-repo" instead.'
            ))->duringInstantiation();
    }

    public function it_throws_an_exception_if_it_is_not_a_one_dot_seven(
        PimConnection $connection,
        ComposerJson $composerJson,
        PimConfiguration $sourcePimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '~1.6']));
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->beConstructedThrough('fromSourcePimConfiguration', [$connection, '/source-pim-real-path', $sourcePimConfiguration]);

        $this->shouldThrow(
            new SourcePimDetectionException(
                'Your PIM version should be 1.7.'
            ))->duringInstantiation();
    }
}
