<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;

/**
 * Helper to fetch table names of a pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface TableNamesFetcher
{
    public function getTableNames(AbstractPim $pim): array;
}
