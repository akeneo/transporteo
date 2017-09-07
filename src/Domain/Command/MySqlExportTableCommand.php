<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Dump a table and prepend by useful unix symbol.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlExportTableCommand extends AbstractCommand implements Command
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

    public static function getPimTableNameDumpPath(Pim $pim, string $tableName): string
    {
        return sprintf('%s%smigration_tool_%s.sql', $pim->absolutePath(), DIRECTORY_SEPARATOR, $tableName);
    }
}
