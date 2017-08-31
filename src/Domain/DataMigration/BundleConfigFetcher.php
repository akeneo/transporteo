<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;

/**
 * Bundle config fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface BundleConfigFetcher
{
    public function fetch(AbstractPim $pim, string $bundleName): array;
}
