<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrate from one table to another.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface TableMigrator
{
    /**
     * @throws DataMigrationException
     */
    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void;
}
