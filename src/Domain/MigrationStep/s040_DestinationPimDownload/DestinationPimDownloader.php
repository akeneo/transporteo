<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Interface to define contract about downloading the pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DestinationPimDownloader
{
    public function download(DownloadMethod $downloadMethod, Pim $pim, ?string $projectName): string;

    public function supports(DownloadMethod $downloadMethod): bool;
}
