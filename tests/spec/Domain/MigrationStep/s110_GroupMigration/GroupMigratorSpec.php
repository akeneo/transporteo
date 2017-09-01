<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration\GroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration\GroupMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GroupMigratorSpec extends ObjectBehavior
{
    public function let(DatabaseQueryExecutor $databaseQueryExecutor)
    {
        $this->beConstructedWith($databaseQueryExecutor);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GroupMigrator::class);
    }

    public function it_sucessfully_migrates_groups(
        DataMigrator $groupMigratorOne,
        DataMigrator $groupMigratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $databaseQueryExecutor
    ) {
        $this->addGroupMigrator($groupMigratorOne);
        $this->addGroupMigrator($groupMigratorTwo);

        $groupMigratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $groupMigratorTwo->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $destinationPim->getDatabaseName()->willReturn('database_name');

        $databaseQueryExecutor->execute(
            'UPDATE database_name.pim_catalog_group_type SET is_variant = 0',
            $destinationPim
        )->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_throws_an_exception_if_an_error_occurred(
        DataMigrator $groupMigratorOne,
        DataMigrator $groupMigratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->addGroupMigrator($groupMigratorOne);
        $this->addGroupMigrator($groupMigratorTwo);

        $groupMigratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $groupMigratorTwo->migrate($sourcePim, $destinationPim)->willThrow(new DataMigrationException());

        $this->shouldThrow(new GroupMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }
}
