<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

/**
 * Representation of a composer.json file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface FileFetcher
{
    /**
     * @return string the local path
     *
     * @throws FileNotFoundException
     * @throws \RuntimeException
     */
    public function fetch(string $path): string;
}
