<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\DataMigration\SimpleDataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for Simple Migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SimpleDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $migrator) {
        $this->beConstructedWith($migrator, 'table_test');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SimpleDataMigrator::class);
    }

    public function it_migrates(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    )
    {
        $migrator->migrate($sourcePim, $destinationPim, 'table_test')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
