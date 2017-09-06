<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;

/**
 * MySQL command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalMySqlQueryExecutor extends AbstractMysqlQueryExecutor implements DatabaseQueryExecutor
{
    public function execute(string $sql, Pim $pim): void
    {
        $pdo = $this->getConnection($pim);

        try {
            $pdo->exec($sql);
        } catch (\PDOException $exception) {
            throw new QueryException(
                sprintf('Query "%s" occured an error : %s', $sql, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function query(string $sql, Pim $pim, int $fetchMode = self::DATA_FETCH): array
    {
        $pdo = $this->getConnection($pim);

        $fetchMode = $fetchMode === self::DATA_FETCH ? \PDO::FETCH_ASSOC : \PDO::FETCH_COLUMN;

        return $pdo->query($sql)->fetchAll($fetchMode);
    }

    protected function getConnection(Pim $pim): \PDO
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

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof Localhost || $connection instanceof DockerConnection;
    }
}
