<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\DumpTableMigrator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use integration\Akeneo\PimMigration\DatabaseSetupedTestCase;

/**
 * Integration test for dump table.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DumpTableMigratorIntegration extends DatabaseSetupedTestCase
{
    public function testDumpTableMigratorError()
    {
        $this->expectException(DataMigrationException::class);

        $sourcePim = new SourcePim('localhost', 3310, 'akeneo_pim', 'akeneo_pim', 'akeneo_pim', null, null, false, null, false, '/a-path', new Localhost());
        $destinationPim = new DestinationPim('localhost', 3311, 'akeneo_pim', 'akeneo_pim', 'akeneo_pim', false, null, 'akeneo_pim', 'localhost', '/a-path', new Localhost());

        $fileFetcherRegistry = new FileFetcherRegistry();
        $fileFetcherRegistry->addFileFetcher(new LocalFileFetcher(new FileSystemHelper()));
        $tableMigrator = new TableMigrator($this->databaseQueryExectuorRegistry, $fileFetcherRegistry);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'a_non_existing_table');
    }
}
