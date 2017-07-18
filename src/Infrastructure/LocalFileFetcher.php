<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Local file fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017  Akeneo SAS (http://www.akeneo.com)
 */
final class LocalFileFetcher implements FileFetcher
{
    /**
     * {@inheritdoc}
     */
    public function fetch(string $path): string
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new FileNotFoundException("The file {$path} does not exist");
        }

        $fileName = pathinfo($path)['basename'];
        $varDir = sprintf(
            '%s%s..%s..%svar',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $localPath = sprintf('%s%s%s', $varDir, DIRECTORY_SEPARATOR, $fileName);

        $fs->copy($path, $localPath);

        return realpath($localPath);
    }
}
