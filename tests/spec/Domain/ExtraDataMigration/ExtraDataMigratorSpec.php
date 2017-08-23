<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\ExtraDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\ExtraDataMigration\ExtraDataMigrationException;
use Akeneo\PimMigration\Domain\ExtraDataMigration\ExtraDataMigrator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ExtraDataMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $tableMigrator, DatabaseQueryExecutor $executor)
    {
        $this->beConstructedWith(
            $tableMigrator,
            $executor
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ExtraDataMigrator::class);
    }

    public function it_throws_an_exception_during_query(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $executor
    ) {
        $executor->query('SHOW TABLES', $sourcePim, DatabaseQueryExecutor::COLUMN_FETCH)->willThrow(new DataMigrationException());

        $this->shouldThrow(new ExtraDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_exception_during_migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $tableMigrator,
        $executor
    ) {
        $executor->query('SHOW TABLES', $sourcePim, DatabaseQueryExecutor::COLUMN_FETCH)->willReturn(['an_unknown_table']);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'an_unknown_table')->willThrow(new DataMigrationException());

        $this->shouldThrow(new ExtraDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_migrate_all_tables(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $tableMigrator,
        $executor
    ) {
        $executor->query('SHOW TABLES', $sourcePim, DatabaseQueryExecutor::COLUMN_FETCH)->willReturn(['an_unknown_table']);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'an_unknown_table')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
