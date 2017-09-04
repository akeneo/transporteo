<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Fetch the current config of a given bundle in a PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface BundleConfigFetcher
{
    public function fetch(Pim $pim, string $bundleName): array;
}
