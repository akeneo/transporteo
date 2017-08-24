<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;

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
    public function execute(string $sql, AbstractPim $pim): void;

    /**
     * @throws QueryException
     */
    public function query(string $sql, AbstractPim $pim, int $fetchMode = self::DATA_FETCH): array;
}
