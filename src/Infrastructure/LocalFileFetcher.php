<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;

/**
 * Local file fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017  Akeneo SAS (http://www.akeneo.com)
 */
class LocalFileFetcher implements FileFetcher
{
    /** @var FileSystemHelper */
    private $fileSystemHelper;

    public function __construct(FileSystemHelper $fileSystemHelper)
    {
        $this->fileSystemHelper = $fileSystemHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(PimConnection $connection, string $filePath, bool $withLocalCopy): string
    {
        if (!$this->fileSystemHelper->fileExists($filePath)) {
            throw new FileNotFoundException("The file {$filePath} does not exist");
        }

        if (false === $withLocalCopy) {
            return $this->fileSystemHelper->getRealPath($filePath);
        }

        $fileName = pathinfo($filePath)['basename'];
        $varDir = sprintf(
            '%s%s..%s..%svar',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $localPath = sprintf('%s%s%s', $varDir, DIRECTORY_SEPARATOR, $fileName);

        $this->fileSystemHelper->copyFile($filePath, $localPath, true);

        return $this->fileSystemHelper->getRealPath($localPath);
    }

    public function supports(PimConnection $pimConnection): bool
    {
        return $pimConnection instanceof Localhost;
    }
}
