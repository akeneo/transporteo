<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s080_FamilyMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutorRegistry;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s080_FamilyMigration\FamilyDataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s080_FamilyMigration\FamilyMigrationException;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Exception for Family Migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $migrator, DatabaseQueryExecutorRegistry $registry)
    {
        $this->beConstructedWith($migrator, $registry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyDataMigrator::class);
    }

    public function it_throws_an_exception_due_to_table_migrator(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');
        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_family')
            ->willThrow(DataMigrationException::class);

        $this->shouldThrow(new FamilyMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_an_exception_due_to_database_query_executor_alter_table(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        DatabaseQueryExecutor $executor,
        $registry,
        $migrator
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');

        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_family')
            ->shouldBeCalled();

        $sqlAddColumnPart = 'ADD COLUMN image_attribute_id INT(11) DEFAULT NULL AFTER label_attribute_id';
        $sqlAddAttributeFkPart = 'ADD CONSTRAINT `FK_90632072BC295696` FOREIGN KEY (`image_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL';
        $sqlAddKeyPart = 'ADD KEY `IDX_90632072BC295696` (`image_attribute_id`)';

        $sql = sprintf(
            'ALTER TABLE %s.%s %s, %s, %s',
            'akeneo_pim_two_for_test',
            'pim_catalog_family',
            $sqlAddColumnPart,
            $sqlAddAttributeFkPart,
            $sqlAddKeyPart
        );

        $registry->get($destinationPim)->willReturn($executor);
        $executor->execute($sql, $destinationPim)->willThrow(QueryException::class);


        $this->shouldThrow(new FamilyMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }


    public function it_throws_nothing(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        DatabaseQueryExecutor $executor,
        $registry,
        $migrator
    ) {
        $destinationPim->getDatabaseName()->willReturn('akeneo_pim_two_for_test');

        $migrator
            ->migrate($sourcePim, $destinationPim, 'pim_catalog_family')
            ->shouldBeCalled();

        $sqlAddColumnPart = 'ADD COLUMN image_attribute_id INT(11) DEFAULT NULL AFTER label_attribute_id';
        $sqlAddAttributeFkPart = 'ADD CONSTRAINT `FK_90632072BC295696` FOREIGN KEY (`image_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL';
        $sqlAddKeyPart ='ADD KEY `IDX_90632072BC295696` (`image_attribute_id`)';

        $registry->get($destinationPim)->willReturn($executor);

        $executor->execute(
            sprintf(
                'ALTER TABLE %s.%s %s, %s, %s',
                'akeneo_pim_two_for_test',
                'pim_catalog_family',
                $sqlAddColumnPart,
                $sqlAddAttributeFkPart,
                $sqlAddKeyPart
            ),
            $destinationPim
        )->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim, 'pim_catalog_family_attribute')->shouldBeCalled();
        $migrator->migrate($sourcePim, $destinationPim, 'pim_catalog_family_translation')->shouldBeCalled();


        $this->migrate($sourcePim, $destinationPim);
    }
}
