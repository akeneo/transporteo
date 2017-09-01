<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\AbstractPim;

/**
 * Able to get a table name from an entity namespace.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface EntityTableNameFetcher
{
    public function fetchTableName(AbstractPim $pim, string $entityNamespace): string;
}
