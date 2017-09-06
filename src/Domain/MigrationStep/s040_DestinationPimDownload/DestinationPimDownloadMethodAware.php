<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload;

/**
 * Defining that a class should know the download method of a PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DestinationPimDownloadMethodAware
{
    public function setDownloadMethod(DownloadMethod $downloadMethod): void;
}
