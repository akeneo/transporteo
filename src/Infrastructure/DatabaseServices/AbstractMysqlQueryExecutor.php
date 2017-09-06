<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Abstract MySQLQueryExecutor.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractMysqlQueryExecutor implements DatabaseQueryExecutor
{
    /** @var ConsoleHelper */
    protected $consoleHelper;

    public function __construct(ConsoleHelper $consoleHelper)
    {
        $this->consoleHelper = $consoleHelper;
    }

    public function exportTable(string $tableName, Pim $pim): void
    {
        $this->consoleHelper->execute(
            $pim,
            new MySqlDumpCommand($tableName, $this->getPimTableNameDumpPath($pim, $tableName))
        );
    }

    public function importTable(string $tableName, Pim $pim): void
    {
        $dumpPath = self::getLocalTableDumpPath($tableName);

        $dumpPath = realpath($dumpPath);

        if (false === $dumpPath) {
            throw new \InvalidArgumentException(sprintf('The file %s does not exist', $dumpPath));
        }

        $this->consoleHelper->execute($pim, new MysqlRawCommand(sprintf('< %s', $dumpPath)));
    }

    public function getLocalTableDumpPath(string $tableName): string
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

    public function getPimTableNameDumpPath(Pim $pim, string $tableName): string
    {
        return sprintf('%s%smigration_tool_%s.sql', $pim->absolutePath(), DIRECTORY_SEPARATOR, $tableName);
    }
}
