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

    /**
     * Fetch all the media files of a given absolute path (i.e. assets and products pictures).
     *
     * @throws FileNotFoundException if the source path does not exists
     */
    public function fetchMediaFiles(PimConnection $connection, string $sourcePath, string $destinationPath): void;

    public function supports(PimConnection $pimConnection): bool;
}
