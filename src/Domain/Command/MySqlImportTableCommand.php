<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Import a table and prepend by useful unix symbol.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlImportTableCommand extends AbstractCommand implements Command
{
    public function __construct(string $path)
    {
        parent::__construct(
            sprintf('< %s', $path)
        );
    }

    public static function getLocalTableDumpPath(string $tableName): string
    {
        return sprintf(
            '%s%s..%s..%s..%svar%smigration_tool_%s.sql',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $tableName
        );
    }
}
