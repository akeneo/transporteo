<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutorRegistry;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\AttributeDataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\StructureMigrationException;
use PhpSpec\ObjectBehavior;

/**
 * Exception for Attribute Data Migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $migrator, ConsoleHelper $consoleHelper)
    {
        $this->beConstructedWith($migrator, $consoleHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AttributeDataMigrator::class);
    }

    public function it_throws_an_exception_due_to_table_migrator(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');
        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_attribute')
            ->willThrow(DataMigrationException::class);

        $this->shouldThrow(new StructureMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_an_exception_due_to_database_query_executor_text_to_textarea(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator,
        $consoleHelper
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');

        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_attribute')
            ->shouldBeCalled();

        $consoleHelper
            ->execute($destinationPim, new MySqlExecuteCommand('UPDATE akeneo_pim_two_for_test.pim_catalog_attribute SET backend_type = "textarea" WHERE backend_type = "text"'))
            ->willThrow(QueryException::class);

        $this->shouldThrow(new StructureMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_an_exception_due_to_database_query_executor_varchar_to_text(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator,
        $consoleHelper
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');

        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_attribute')
            ->shouldBeCalled();

        $consoleHelper
            ->execute($destinationPim, new MySqlExecuteCommand('UPDATE akeneo_pim_two_for_test.pim_catalog_attribute SET backend_type = "textarea" WHERE backend_type = "text"'))
            ->shouldBeCalled();

        $consoleHelper
            ->execute($destinationPim, new MySqlExecuteCommand('UPDATE akeneo_pim_two_for_test.pim_catalog_attribute SET backend_type = "text" WHERE backend_type = "varchar"'))
            ->willThrow(QueryException::class);

        $this->shouldThrow(new StructureMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_nothing(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator,
        $consoleHelper
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');

        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_attribute')
            ->shouldBeCalled();

        $consoleHelper
            ->execute($destinationPim, new MySqlExecuteCommand('UPDATE akeneo_pim_two_for_test.pim_catalog_attribute SET backend_type = "textarea" WHERE backend_type = "text"'))
            ->shouldBeCalled();

        $consoleHelper
            ->execute($destinationPim, new MySqlExecuteCommand('UPDATE akeneo_pim_two_for_test.pim_catalog_attribute SET backend_type = "text" WHERE backend_type = "varchar"'))
            ->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
