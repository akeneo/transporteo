<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Extract an archive and copy it.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalArchiveDestinationPimDownloader implements DestinationPimDownloader
{
    public function download(DownloadMethod $downloadMethod, Pim $pim, string $projectName): string
    {
        if (!$downloadMethod instanceof Archive) {
            throw new \InvalidArgumentException(sprintf('Expected %s, %s given', Archive::class, get_class($downloadMethod)));
        }
        $destinationPath = sprintf(
            '%s%s%s%s%s%s%s%s%s%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            'var',
            DIRECTORY_SEPARATOR
        );

        $destinationPath = realpath($destinationPath).DIRECTORY_SEPARATOR.$projectName;

        $archive = new \PharData($downloadMethod->getLocalArchivePath());

        try {
            $archive->extractTo($destinationPath, null, true);
        } catch (\Exception $exception) {
            throw new \RuntimeException(sprintf('Impossible to extract the archive : %s', $exception->getMessage()), $exception->getCode(), $exception);
        }

        return $destinationPath;
    }

    public function supports(DownloadMethod $downloadMethod): bool
    {
        return $downloadMethod instanceof Archive;
    }
}
