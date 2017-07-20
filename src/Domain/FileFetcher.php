<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

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
     */
    public function fetch(string $path): string;
}
