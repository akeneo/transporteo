<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Registry of fetchers that give the right one depending on the Connection.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FileFetcherRegistry
{
    /** @var FileFetcher[] */
    private $fileFetchers;

    /**
     *  @throws FileNotFoundException when the file to fetch does not exist
     */
    public function fetch(PimConnection $connection, string $filePath, bool $withLocalCopy): string
    {
        return $this->get($connection)->fetch($connection, $filePath, $withLocalCopy);
    }

    public function addFileFetcher(FileFetcher $fileFetcher): void
    {
        $this->fileFetchers[] = $fileFetcher;
    }

    protected function get(PimConnection $connection): FileFetcher
    {
        foreach ($this->fileFetchers as $fileFetcher) {
            if ($fileFetcher->supports($connection)) {
                return $fileFetcher;
            }
        }

        throw new \InvalidArgumentException('The connection is not supported by any file fetchers.');
    }
}
