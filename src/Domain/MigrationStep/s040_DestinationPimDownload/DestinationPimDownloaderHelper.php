<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Helper which give the Downloader corresponding to a DownloadMethod.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloaderHelper
{
    /** @var DestinationPimDownloader[] */
    private $destinationPimDownloaders = [];

    public function download(DownloadMethod $downloadMethod, Pim $pim, ?string $projectName): string
    {
        return $this->get($downloadMethod)->download($downloadMethod, $pim, $projectName);
    }

    public function addDestinationPimDownloader(DestinationPimDownloader $destinationPimDownloader): void
    {
        $this->destinationPimDownloaders[] = $destinationPimDownloader;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function get(DownloadMethod $downloadMethod): DestinationPimDownloader
    {
        foreach ($this->destinationPimDownloaders as $destinationPimDownloader) {
            if ($destinationPimDownloader->supports($downloadMethod)) {
                return $destinationPimDownloader;
            }
        }

        throw new \InvalidArgumentException('The download method is not supported by any DestinationPimDownloader');
    }
}
