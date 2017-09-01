<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\TableNamesFetcher;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration\ExtraDataMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration\ExtraDataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\UnsuccessfulCommandException;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ExtraDataMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $tableMigrator, TableNamesFetcher $tableNamesFetcher)
    {
        $this->beConstructedWith(
            $tableMigrator,
            $tableNamesFetcher
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ExtraDataMigrator::class);
    }

    public function it_throws_an_exception_during_getting_table(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $tableNamesFetcher
    ) {
        $tableNamesFetcher->getTableNames($sourcePim)->willThrow(new UnsuccessfulCommandException());

        $this->shouldThrow(new ExtraDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_exception_during_migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $tableMigrator,
        $tableNamesFetcher
    ) {
        $tableNamesFetcher->getTableNames($sourcePim)->willReturn(['an_unknown_table']);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'an_unknown_table')->willThrow(new DataMigrationException());

        $this->shouldThrow(new ExtraDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_migrate_all_tables(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $tableMigrator,
        $tableNamesFetcher
    ) {
        $tableNamesFetcher->getTableNames($sourcePim)->willReturn(['an_unknown_table']);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'an_unknown_table')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}