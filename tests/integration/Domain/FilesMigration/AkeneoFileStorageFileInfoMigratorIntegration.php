<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\FilesMigration;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
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
        $sourcePim = new SourcePim('localhost', 3310, 'akeneo_pim', 'akeneo_pim', 'akeneo_pim', null, null, false, null, false);
        $destinationPim = new DestinationPim('localhost', 3311, 'akeneo_pim', 'akeneo_pim', 'akeneo_pim', false, null, 'akeneo_pim', 'localhost', '/a-path');

        $akeneoFileStorageFileInfoMigrator = new AkeneoFileStorageFileInfoMigrator(new DumpTableMigrator(new LocalCommandLauncherFactory()));
        $akeneoFileStorageFileInfoMigrator->migrate($sourcePim, $destinationPim);

        $sourcePimConnection = $this->getConnection($sourcePim, true);
        $destinationPimConnection = $this->getConnection($destinationPim, true);

        $sourcePimRecords = $sourcePimConnection->query('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info')->fetchAll();
        $destinationPimRecords = $destinationPimConnection->query('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info')->fetchAll();

        $this->assertEquals($sourcePimRecords, $destinationPimRecords);
    }
}
