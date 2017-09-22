<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrates the media of the products.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMediaMigrator implements DataMigrator
{
    /** @var FileFetcherRegistry */
    private $fileFetcher;

    public function __construct(FileFetcherRegistry $fileFetcher)
    {
        $this->fileFetcher = $fileFetcher;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->fileFetcher->fetchMediaFiles(
            $sourcePim->getConnection(),
            $sourcePim->getCatalogStorageDir(),
            $destinationPim->getCatalogStorageDir()
        );
    }
}
