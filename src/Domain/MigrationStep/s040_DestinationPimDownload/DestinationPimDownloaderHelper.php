<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload;

use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Helper which give the Downloader corresponding to a DownloadMethod.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloaderHelper implements DestinationPimDownloadMethodAware
{
    /** @var DestinationPimDownloader[] */
    private $destinationPimDownloaders = [];

    /** @var DownloadMethod */
    private $downloadMethod;

    public function download(SourcePim $pim, string $projectName): string
    {
        return $this->get()->download($this->downloadMethod, $pim, $projectName);
    }

    public function addDestinationPimDownloader(DestinationPimDownloader $destinationPimDownloader): void
    {
        $this->destinationPimDownloaders[] = $destinationPimDownloader;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function get(): DestinationPimDownloader
    {
        foreach ($this->destinationPimDownloaders as $destinationPimDownloader) {
            if ($destinationPimDownloader->supports($this->downloadMethod)) {
                return $destinationPimDownloader;
            }
        }

        throw new \InvalidArgumentException('The download method is not supported by any DestinationPimDownloader');
    }

    public function setDownloadMethod(DownloadMethod $downloadMethod): void
    {
        $this->downloadMethod = $downloadMethod;
    }
}
