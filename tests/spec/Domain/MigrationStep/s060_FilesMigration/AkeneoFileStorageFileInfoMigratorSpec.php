<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s060_FilesMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s060_FilesMigration\FilesMigrationException;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for AkeneoFileStorageFileInfo.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $migrator)
    {
        $this->beConstructedWith($migrator);
    }

    public function it_throws_an_exception_due_to_table_migrator(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    )
    {
        $migrator
            ->migrate($sourcePim, $destinationPim, 'akeneo_file_storage_file_info')
            ->willThrow(DataMigrationException::class);

        $this->shouldThrow(new FilesMigrationException(''))->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_should_not_throw_an_exception(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    ) {
        $migrator->migrate($sourcePim, $destinationPim, 'akeneo_file_storage_file_info')->shouldBeCalled();
        $this->shouldNotThrow(new FilesMigrationException(''))->during('migrate', [$sourcePim, $destinationPim]);
    }
}
