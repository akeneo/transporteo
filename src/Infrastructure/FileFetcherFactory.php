<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;

/**
 * File Fetcher Factory.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FileFetcherFactory
{
    public function createSshFileFetcher(ServerAccessInformation $serverAccessInformation): FileFetcher
    {
        $rsa = new RSA();
        $rsa->load($serverAccessInformation->getSshKey()->getKey());

        return new SshFileFetcher(
            $serverAccessInformation,
            new SFTP($serverAccessInformation->getHost(), $serverAccessInformation->getPort()),
            $rsa
        );
    }

    public function createLocalFileFetcher(): FileFetcher
    {
        return new LocalFileFetcher();
    }

    public function createWithoutCopyLocalFileFetcher(): FileFetcher
    {
        return new WithoutCopyLocalFileFetcher();
    }
}
