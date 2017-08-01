<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InstalledDestinationPimDownloader implements DestinationPimDownloader
{
    /** @var string */
    private $installedPimLocation;

    public function __construct(string $installedPimLocation)
    {
        $this->installedPimLocation = $installedPimLocation;
    }

    public function download(SourcePim $pim, string $projectName): string
    {
        $fs = new Filesystem();

        $repositoryPath = sprintf(
            '%s%s%s%s%s%s%s%s%s%s%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            'var',
            DIRECTORY_SEPARATOR,
            $projectName
        );

        $fs->mkdir($repositoryPath);

        $fs->mirror($this->installedPimLocation, $repositoryPath);

        return $repositoryPath;
    }
}
