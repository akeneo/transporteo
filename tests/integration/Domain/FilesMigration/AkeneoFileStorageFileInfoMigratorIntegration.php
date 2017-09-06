<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\FilesMigration;

use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s060_FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use integration\Akeneo\PimMigration\DatabaseSetupedTestCase;

/**
 * Integration test for the FileMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigratorIntegration extends DatabaseSetupedTestCase
{
    public function testItCopyTheAkeneoFileStorageFileInfoTable()
    {
        $fileFetcherRegistry = new FileFetcherRegistry();
        $fileFetcherRegistry->addFileFetcher(new LocalFileFetcher(new FileSystemHelper()));

        $tableMigrator = new TableMigrator($this->databaseQueryExectuorRegistry, $fileFetcherRegistry);

        $akeneoFileStorageFileInfoMigrator = new AkeneoFileStorageFileInfoMigrator($tableMigrator);
        $akeneoFileStorageFileInfoMigrator->migrate($this->sourcePim, $this->destinationPim);

        $sourcePimConnection = $this->getConnection($this->sourcePim, true);
        $destinationPimConnection = $this->getConnection($this->destinationPim, true);

        $sourcePimRecords = $sourcePimConnection->query('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info')->fetchAll();
        $destinationPimRecords = $destinationPimConnection->query('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info')->fetchAll();

        $this->assertEquals($sourcePimRecords, $destinationPimRecords);
    }
}
