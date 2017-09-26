<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\Pim\Api\ProductApiInterface;
use Akeneo\PimMigration\Domain\Command\Api\ListAllProductsCommand;
use Akeneo\PimMigration\Domain\Command\Api\UpsertListProductsCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration\ProductAssociationMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductAssociationMigratorSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console, LoggerInterface $logger)
    {
        $this->beConstructedWith(2, $console, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductAssociationMigrator::class);
    }

    public function it_successfully_migrates_product_associations(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        CommandResult $listProductsResult,
        CommandResult $upsertProductsResult,
        $console
    )
    {
        $products = $this->getSourceProducts();

        $console->execute(new ListAllProductsCommand(2), $sourcePim)->willReturn($listProductsResult);
        $listProductsResult->getOutput()->willReturn($products);

        $upsertProducts = new UpsertListProductsCommand([
            [
                'identifier'   => 'a_product_with_associations',
                'associations' => ['a_product', 'another_product']
            ],
            [
                'identifier'   => 'another_product_with_associations',
                'associations' => ['a_product_with_associations']
            ]
        ]);

        $console->execute($upsertProducts, $destinationPim)->willReturn($upsertProductsResult);

        $upsertProductsResult->getOutput()->willReturn([
            ['status_code' => 204],
            ['status_code' => 204]
        ]);

        $this->migrate($sourcePim, $destinationPim);
    }

    private function getSourceProducts()
    {
        return $products = [
            [
                'identifier'    => 'product_without_associations',
                'family'        => 'clothes',
                'groups'        => ['tshirt_group'],
                'categories'    => ['tshirts'],
                'created'       => '2017-09-01T12:45:41+00:00',
                'values'        => [
                    'name' => [
                        'locale' => null,
                        'sope'   => null,
                        'data'   => 'A product without associations'
                    ]
                ],
                'associations'  => [],
            ],
            [
                'identifier'    => 'a_product_with_associations',
                'family'        => 'clothes',
                'groups'        => [],
                'categories'    => ['tshirts'],
                'created'       => '2017-09-01T12:48:56+00:00',
                'values'        => [],
                'associations'  => ['a_product', 'another_product'],
            ],
            [
                'identifier'    => 'another_product_with_associations',
                'family'        => 'clothes',
                'groups'        => [],
                'categories'    => ['tshirts'],
                'created'       => '2017-09-01T12:49:56+00:00',
                'values'        => [],
                'associations'  => ['a_product_with_associations'],
            ],
        ];
    }
}
