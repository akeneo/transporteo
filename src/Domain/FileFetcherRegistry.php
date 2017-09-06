<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

use Akeneo\PimMigration\Domain\Pim\DestinationPimConnected;
use Akeneo\PimMigration\Domain\Pim\DestinationPimConnectionAware;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Pim\SourcePimConnected;
use Akeneo\PimMigration\Domain\Pim\SourcePimConnectionAware;

/**
 * .
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FileFetcherRegistry implements DestinationPimConnectionAware, SourcePimConnectionAware
{
    use SourcePimConnected;
    use DestinationPimConnected;

    /** @var FileFetcher[] */
    private $fileFetchers;

    /**
     *  @throws FileNotFoundException when the file to fetch does not exist
     */
    public function fetch(Pim $pim, string $filePath, bool $withLocalCopy): string
    {
        $connection = $pim instanceof SourcePim ? $this->sourcePimConnection : $this->destinationPimConnection;

        return $this->get($connection)->fetch($connection, $filePath, $withLocalCopy);
    }

    public function fetchSource(string $filePath, bool $withLocalCopy): string
    {
        return $this->get($this->sourcePimConnection)->fetch($this->sourcePimConnection, $filePath, $withLocalCopy);
    }

    public function fetchDestination(string $filePath, bool $withLocalCopy): string
    {
        return $this->get($this->destinationPimConnection)->fetch($this->destinationPimConnection, $filePath, $withLocalCopy);
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
