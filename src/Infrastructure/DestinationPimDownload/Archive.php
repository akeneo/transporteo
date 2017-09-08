<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;

/**
 * Representation of an Archive DownloadMethod.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Archive implements DownloadMethod
{
    /** @var string */
    private $localArchivePath;

    public function __construct(string $localArchivePath)
    {
        $this->localArchivePath = $localArchivePath;

        if (!$this->endsByTarDotGz($localArchivePath)) {
            throw new \InvalidArgumentException('Your archive must be a .tar.gz');
        }
    }

    public function getLocalArchivePath(): string
    {
        return $this->localArchivePath;
    }

    private function endsByTarDotGz(string $localArchivePath): bool
    {
        $extension = '.tar.gz';

        return  substr_compare($localArchivePath, $extension, -strlen($extension)) === 0;
    }
}
