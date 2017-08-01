<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Extract an archive and copy it.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalArchiveDestinationPimDownloader implements DestinationPimDownloader
{
    /** @var string */
    protected $localArchivePath;

    public function __construct(string $localArchivePath)
    {
        if (!$this->endsByTarDotGz($localArchivePath)) {
            throw new \InvalidArgumentException('Your archive must be a .tar.gz');
        }

        $this->localArchivePath = $localArchivePath;
    }

    public function download(SourcePim $pim, string $projectName): string
    {
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

        $archive = new \PharData($this->localArchivePath);

        try {
            $archive->extractTo($destinationPath, null, true);
        } catch (\Exception $exception) {
            throw new \RuntimeException(sprintf('Impossible to extract the archive : %s', $exception->getMessage()), $exception->getCode(), $exception);
        }

        return $destinationPath;
    }

    private function endsByTarDotGz(string $localArchivePath): bool
    {
        $extension = '.tar.gz';

        return  substr_compare($localArchivePath, $extension, -strlen($extension)) === 0;
    }
}
