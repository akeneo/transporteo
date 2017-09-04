<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\Pim;

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

    /**
     * @throws QueryException
     */
    public function query(string $sql, Pim $pim, int $fetchMode = self::DATA_FETCH): array;
}
