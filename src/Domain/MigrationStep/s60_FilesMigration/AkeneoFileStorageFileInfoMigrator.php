<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s60_FilesMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;

/**
 * Migrate the `akeneo_file_storage_file_info_table`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $naiveMigrator;

    public function __construct(TableMigrator $naiveMigrator)
    {
        $this->naiveMigrator = $naiveMigrator;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $this->naiveMigrator->migrate($sourcePim, $destinationPim, 'akeneo_file_storage_file_info');
        } catch (DataMigrationException $exception) {
            throw new FilesMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
