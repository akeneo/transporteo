<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\ExtraDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration\ExtraDataMigrator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use integration\Akeneo\PimMigration\DatabaseSetupedTestCase;

/**
 * Integration test for Extra Data Migration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigrationIntegration extends DatabaseSetupedTestCase
{
    public function testItCopyUnknownTable() {
        $fileFetcherRegistry = new FileFetcherRegistry();
        $fileFetcherRegistry->addFileFetcher(new LocalFileFetcher(new FileSystemHelper()));
        $fileFetcherRegistry->connectSourcePim(new Localhost());
        $fileFetcherRegistry->connectDestinationPim(new Localhost());

        $tableMigrator = new TableMigrator($this->databaseQueryExectuorRegistry, $fileFetcherRegistry);

        $extraDataMigrator = new ExtraDataMigrator(
            $tableMigrator,
            $this->databaseQueryExectuorRegistry
        );

        $this->assertNotContains('acme_reference_data_color', $this->getDestinationPimTables());
        $extraDataMigrator->migrate($this->sourcePim, $this->destinationPim);
        $this->assertContains('acme_reference_data_color', $this->getDestinationPimTables());
    }

    private function getDestinationPimTables(): array
    {
        return $this->getConnection($this->destinationPim, true)->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
    }
}
