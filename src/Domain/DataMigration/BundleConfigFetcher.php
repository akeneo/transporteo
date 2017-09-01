<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\AbstractPim;

/**
 * Fetch the current config of a given bundle in a PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface BundleConfigFetcher
{
    public function fetch(AbstractPim $pim, string $bundleName): array;
}
