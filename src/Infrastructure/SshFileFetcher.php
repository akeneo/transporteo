<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
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
    /**
     * {@inheritdoc}
     */
    public function fetch(PimConnection $connection, string $filePath, bool $withLocalCopy): string
    {
        $sftp = $this->createSftpConnection($connection);

        $pathInfo = pathinfo($filePath);
        $fileName = $pathInfo['basename'];

        $subList = $sftp->nlist($pathInfo['dirname']);

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

        $result = $sftp->get($filePath, $localPath);

        if (false === $result) {
            throw new FileNotFoundException("The file {$filePath} is not reachable", $filePath);
        }

        return $localPath;
    }

    public function supports(PimConnection $pimConnection): bool
    {
        return $pimConnection instanceof SshConnection;
    }

    private function createSftpConnection(PimConnection $connection): SFTP
    {
        if (!$connection instanceof SshConnection) {
            throw new \InvalidArgumentException(sprintf('Expected %s, %s given', SshConnection::class, get_class($connection)));
        }

        $key = new RSA();
        $key->load($connection->getSshKey()->getKey());
        $sftp = new SFTP($connection->getHost(), $connection->getPort());

        if (!$sftp->isConnected()) {
            if (!$sftp->login($connection->getUsername(), $key)) {
                throw new ImpossibleConnectionException(
                    sprintf(
                        'Impossible to login to %s@%s:%d using this ssh key : %s',
                        $connection->getUsername(),
                        $connection->getHost(),
                        $connection->getPort(),
                        $connection->getSshKey()->getPath()
                    )
                );
            }
        }

        return $sftp;
    }
}
