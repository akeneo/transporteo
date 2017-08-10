<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;

/**
 * Ssh implementation of a file fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshFileFetcher implements FileFetcher
{
    /** @var ServerAccessInformation */
    private $serverAccessInformation;

    /** @var SFTP */
    private $sftp;

    /** @var RSA */
    private $key;

    public function __construct(ServerAccessInformation $serverAccessInformation, SFTP $sftp, RSA $key)
    {
        $this->serverAccessInformation = $serverAccessInformation;
        $this->sftp = $sftp;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $filePath): string
    {
        if (!$this->sftp->isConnected()) {
            if (!$this->sftp->login($this->serverAccessInformation->getUsername(), $this->key)) {
                throw new ImpossibleConnectionException(
                    sprintf(
                        'Impossible to login to %s@%s:%d using this ssh key : %s',
                        $this->serverAccessInformation->getUsername(),
                        $this->serverAccessInformation->getHost(),
                        $this->serverAccessInformation->getPort(),
                        $this->serverAccessInformation->getSshKey()->getPath()
                    )
                );
            }
        }

        $pathInfo = pathinfo($filePath);
        $fileName = $pathInfo['basename'];

        $subList = $this->sftp->nlist($pathInfo['dirname']);

        $filesMatchingName = array_filter($subList, function ($element) use ($fileName) {
            return $element == $fileName;
        });

        if (0 === count($filesMatchingName)) {
            throw new FileNotFoundException("The file {$filePath} does not exist", $filePath);
        }

        $varDir = sprintf(
            '%s%s..%s..%svar',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $localPath = realpath($varDir).DIRECTORY_SEPARATOR.$fileName;

        $result = $this->sftp->get($filePath, $localPath);

        if (false === $result) {
            throw new FileNotFoundException("The file {$filePath} is not reachable", $filePath);
        }

        return $localPath;
    }
}
