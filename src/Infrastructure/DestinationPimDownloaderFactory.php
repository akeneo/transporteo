<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\InstalledDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\LocalArchiveDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\GitDestinationPimDownloader;

/**
 * Factory for all types of downloader.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloaderFactory
{
    public function createLocalArchiveDestinationPimDownloader(string $archivePath): LocalArchiveDestinationPimDownloader
    {
        return new LocalArchiveDestinationPimDownloader($archivePath);
    }

    public function createGitDestinationPimDownloader(): GitDestinationPimDownloader
    {
        return new GitDestinationPimDownloader();
    }

    public function createInstalledDestinationPimDownload(string $localPath): InstalledDestinationPimDownloader
    {
        return new InstalledDestinationPimDownloader($localPath);
    }
}
