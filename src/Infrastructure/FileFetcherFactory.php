<?php

declare(strict_types=1);


namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileFetcher;

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
        return new SshFileFetcher($serverAccessInformation);
    }

    public function createLocalFileFetcher(): FileFetcher
    {
        return new LocalFileFetcher();
    }
}
