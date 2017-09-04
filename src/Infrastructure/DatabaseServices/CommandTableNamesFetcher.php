<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Akeneo\PimMigration\Domain\DataMigration\TableNamesFetcher;

/**
 * Give table name fetcher .
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandTableNamesFetcher implements TableNamesFetcher
{
    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    public function getTableNames(Pim $pim): array
    {
        $commandResult = $this->commandLauncher->runCommand(new ShowTablesCommand($pim), null, false);

        $tables = array_filter(explode(PHP_EOL, $commandResult->getOutput()), function ($element) {
            return !empty(trim($element));
        });

        return $tables;
    }
}
