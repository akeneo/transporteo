<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;

/**
 * MySQL command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlQueryExecutor implements DatabaseQueryExecutor
{
    public function execute(string $sql, AbstractPim $pim): void
    {
        $dsn = sprintf(
            'mysql: host=%s;dbname=%s;port=%s',
            $pim->getMysqlHost(),
            $pim->getDatabaseName(),
            strval($pim->getMysqlPort())
        );

        $pdo = new \PDO(
            $dsn,
            $pim->getDatabaseUser(),
            $pim->getDatabasePassword()
        );

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        try {
            $pdo->exec($sql);
        } catch (\PDOException $exception) {
            throw new QueryException(
                sprintf('Query "%s" occured an error : %s', $sql, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        $pdo = null;
    }
}
