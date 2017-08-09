<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Local file fetcher without performing a copy.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017  Akeneo SAS (http://www.akeneo.com)
 */
class WithoutCopyLocalFileFetcher implements FileFetcher
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $filePath): string
    {
        $fs = new Filesystem();

        if (!$fs->exists($filePath)) {
            throw new FileNotFoundException("The file {$filePath} does not exist");
        }

        return realpath($filePath);
    }
}
