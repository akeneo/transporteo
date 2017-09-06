<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;

/**
 * Representation of a Local DownloadMethod which does not download anything.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Local implements DownloadMethod
{
    /** @var string */
    private $localPath;

    public function __construct(string $localPath)
    {
        $this->localPath = $localPath;
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }
}
