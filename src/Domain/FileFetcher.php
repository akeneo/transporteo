<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Ability to fetch a file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface FileFetcher
{
    /**
     * @return string the local path
     *
     * @throws FileNotFoundException when the file to fetch does not exist
     * @throws \RuntimeException     when something else happen
     */
    public function fetch(PimConnection $connection, string $filePath, bool $withLocalCopy): string;

    public function supports(PimConnection $pimConnection): bool;
}
