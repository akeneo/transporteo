<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\FilesMigration;

use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s60_FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\DumpTableMigrator;
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
        $akeneoFileStorageFileInfoMigrator = new AkeneoFileStorageFileInfoMigrator(new DumpTableMigrator(new LocalCommandLauncherFactory()));
        $akeneoFileStorageFileInfoMigrator->migrate($this->sourcePim, $this->destinationPim);

        $sourcePimConnection = $this->getConnection($this->sourcePim, true);
        $destinationPimConnection = $this->getConnection($this->destinationPim, true);

        $sourcePimRecords = $sourcePimConnection->query('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info')->fetchAll();
        $destinationPimRecords = $destinationPimConnection->query('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info')->fetchAll();

        $this->assertEquals($sourcePimRecords, $destinationPimRecords);
    }
}
