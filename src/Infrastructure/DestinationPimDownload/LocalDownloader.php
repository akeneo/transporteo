<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalDownloader implements DestinationPimDownloader
{
    public function download(DownloadMethod $downloadMethod, Pim $pim, string $projectName): string
    {
        if (!$downloadMethod instanceof  Local) {
            throw new \InvalidArgumentException(sprintf('Expected %s, %s given', Local::class, get_class($downloadMethod)));
        }

        return $downloadMethod->getLocalPath();
    }

    public function supports(DownloadMethod $downloadMethod): bool
    {
        return $downloadMethod instanceof Local;
    }
}
