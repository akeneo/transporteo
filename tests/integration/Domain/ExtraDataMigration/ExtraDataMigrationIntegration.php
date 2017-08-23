<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\ExtraDataMigration;

use Akeneo\PimMigration\Domain\ExtraDataMigration\ExtraDataMigrator;
use Akeneo\PimMigration\Infrastructure\Command\BasicCommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandExecutor;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\DumpTableMigrator;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\MySqlQueryExecutor;
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
        $extraDataMigrator = new ExtraDataMigrator(
            new DumpTableMigrator(new LocalCommandLauncherFactory()),
            new BasicCommandLauncher(new LocalCommandExecutor())
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
