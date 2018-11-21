<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Cli\Ssh;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;

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
        $ssh = new Ssh($connection->getHost(), $connection->getPort());

        $pathInfo = pathinfo($filePath);
        $fileName = $pathInfo['basename'];

        $output = $ssh->exec(
            sprintf('test -f %s ; echo "$?"', $filePath),
            $connection->getUsername(),
            $connection->getPassword()
        );

        if ("0" !== trim($output)) {
            throw new FileNotFoundException("The file {$filePath} does not exist", $filePath);
        }

        $varDir = sprintf('%s/../../var', __DIR__);
        $localPath = realpath($varDir).DIRECTORY_SEPARATOR.$fileName;

        $sshConnection = $ssh->getAuthenticatedConnection($connection->getUsername(), $connection->getPassword());
        $result = ssh2_scp_recv($sshConnection, $filePath, $localPath);
        $ssh->disconnect($sshConnection);

        if (false === $result) {
            throw new FileNotFoundException("The file {$filePath} is not reachable", $filePath);
        }

        return $localPath;
    }

    public function supports(PimConnection $pimConnection): bool
    {
        return $pimConnection instanceof SshConnection;
    }
}
