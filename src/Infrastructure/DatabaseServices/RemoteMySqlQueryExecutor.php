<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class RemoteMySqlQueryExecutor extends AbstractMysqlQueryExecutor implements DatabaseQueryExecutor
{
    /**
     * @throws QueryException
     */
    public function execute(string $sql, Pim $pim): void
    {
        $this->consoleHelper->execute($pim, new MySqlQueryCommand($sql));
    }

    /**
     * @throws QueryException
     */
    public function query(string $sql, Pim $pim, int $fetchMode = self::DATA_FETCH): array
    {
        $output = $this->consoleHelper->execute($pim, new MySqlQueryCommand($sql));

        $lines = array_filter(explode(PHP_EOL, $output->getOutput()), function ($element) {
            return !empty(trim($element));
        });

        $results = [];

        $columns = str_getcsv(array_shift($lines), "\t");

        foreach ($lines as $line) {
            $cells = str_getcsv($line, "\t");
            $results[] = array_combine($columns, $cells);
        }

        return $results;
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof SshConnection;
    }
}
