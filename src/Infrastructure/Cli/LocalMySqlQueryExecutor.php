<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * @internal
 *
 * MySQL command launcher for Local using PDO
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalMySqlQueryExecutor
{
    public function execute(string $sql, Pim $pim): void
    {
        $pdo = $this->getConnection($pim);

        try {
            $pdo->exec($sql);
        } catch (\PDOException $exception) {
            throw new QueryException(sprintf('Query "%s" occured an error : %s', $sql, $exception->getMessage()));
        }
    }

    public function query(string $sql, Pim $pim): array
    {
        $pdo = $this->getConnection($pim);

        return $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getConnection(Pim $pim): \PDO
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

        return $pdo;
    }
}
