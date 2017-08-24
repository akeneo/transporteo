<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Infrastructure\Command\BasicCommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandExecutor;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\CommandTableNamesFetcher;
use integration\Akeneo\PimMigration\DatabaseSetupedTestCase;

/**
 * Integration test for table names fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandTableNamesFetcherIntegration extends DatabaseSetupedTestCase
{
    public function testItFetchTables()
    {
        $commandTableNamesFetcher = new CommandTableNamesFetcher(new BasicCommandLauncher(new LocalCommandExecutor()));

        $result = $commandTableNamesFetcher->getTableNames($this->sourcePim);

        $this->assertNotEmpty($result);
    }
}
