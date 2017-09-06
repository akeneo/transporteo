<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Command\AbstractCommand;
use Akeneo\PimMigration\Domain\Command\Command;

/**
 * Dump a table and prepend by useful unix symbol.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlDumpCommand extends AbstractCommand implements Command
{
    public function __construct(string $tableName, string $path)
    {
        parent::__construct(
            sprintf(
                '%s > %s',
                $tableName,
                $path
            )
        );
    }
}
