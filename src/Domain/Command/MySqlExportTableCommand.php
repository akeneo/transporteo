<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

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

    public static function getPimTableNameDumpPath(string $tableName): string
    {
        return sprintf('%stmp%stransporteo_%s.sql', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $tableName);
    }
}
