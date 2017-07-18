<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use phpseclib\Net\SFTP;

/**
 * Ssh implementation of a file fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class SshFileFetcher implements FileFetcher
{
    /** @var SFTP */
    private $sftp;

    public function __construct(SFTP $sftp)
    {
        $this->sftp = $sftp;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $path): string
    {
        $pathInfo = pathinfo($path);
        $fileName = $pathInfo['basename'];

        $subList = $this->sftp->nlist($pathInfo['dirname']);

        $filesMatchingName = array_filter($subList, function ($element) use ($fileName) {
            return $element == $fileName;
        });

        if (0 === count($filesMatchingName)) {
            throw new FileNotFoundException("The file {$path} does not exist");
        }

        $varDir = sprintf(
            '%s%s..%s..%svar',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $localPath = realpath($varDir).DIRECTORY_SEPARATOR.$fileName;

        $result = $this->sftp->get($path, $localPath);

        if (false === $result) {
            throw new \RuntimeException("The file {$path} is not reachable");
        }

        return $localPath;
    }
}
