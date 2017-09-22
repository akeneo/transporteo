<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration\ProductMediaMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMediaMigratorSpec extends ObjectBehavior
{
    public function let(FileFetcherRegistry $fileFetcher)
    {
        $this->beConstructedWith($fileFetcher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductMediaMigrator::class);
    }

    public function it_migrates_successfully_product_media(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        PimConnection $pimConnection,
        $fileFetcher
    )
    {
        $sourcePim->getConnection()->willReturn($pimConnection);
        $sourcePim->getCatalogStorageDir()->willReturn('/source/storage/catalog');
        $destinationPim->getCatalogStorageDir()->willReturn('/destination/storage/catalog');

        $fileFetcher
            ->fetchMediaFiles($pimConnection, '/source/storage/catalog', '/destination/storage/catalog')
            ->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
