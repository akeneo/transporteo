<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Interface to represent a query executor dedicated to a database.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DatabaseQueryExecutor
{
    public const COLUMN_FETCH = 0;
    public const DATA_FETCH = 1;

    /**
     * @throws QueryException
     */
    public function execute(string $sql, Pim $pim): void;

    public function exportTable(string $tableName, Pim $pim): void;

    public function importTable(string $tableName, Pim $pim): void;

    /**
     * @throws QueryException
     */
    public function query(string $sql, Pim $pim, int $fetchMode = self::DATA_FETCH): array;

    public function supports(PimConnection $connection): bool;

    public function getLocalTableDumpPath(string $tableName): string;

    public function getPimTableNameDumpPath(Pim $pim, string $tableName): string;
}
