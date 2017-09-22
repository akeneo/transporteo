<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\Command\Api\ListAllProductsCommand;
use Akeneo\PimMigration\Domain\Command\Api\UpsertListProductsCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration\ProductMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMigratorSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console, DataMigrator $productMediaMigrator, DataMigrator $productAssociationMigrator, LoggerInterface $logger)
    {
        $this->beConstructedWith(2, $console, $productMediaMigrator, $productAssociationMigrator, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductMigrator::class);
    }

    public function it_successfully_migrates_products(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        CommandResult $listProductsResult,
        CommandResult $firstUpsertProductsResult,
        CommandResult $secondUpsertProductsResult,
        $console,
        $productMediaMigrator,
        $productAssociationMigrator
    )
    {
        $destinationPim->getDatabaseName()->willReturn('destination_database');

        $productMediaMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $productAssociationMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $products = $this->getSourceProducts();

        $console->execute(new ListAllProductsCommand(2), $sourcePim)->willReturn($listProductsResult);
        $listProductsResult->getOutput()->willReturn($products);

        $firstUpsertProducts = new UpsertListProductsCommand([
            [
                'identifier' => 'simple_product',
                'family'     => 'clothes',
                'groups'     => ['tshirt_group'],
                'categories' => ['tshirts'],
                'created'    => '2017-09-01T12:45:41+00:00',
                'values'     => [
                    'name' => [
                        'locale' => null,
                        'sope'   => null,
                        'data'   => 'Simple product'
                    ]
                ]
            ],
            [
                'identifier' => 'product_with_associations',
                'family'     => 'clothes',
                'groups'     => [],
                'categories' => ['tshirts'],
                'created'    => '2017-09-01T12:48:56+00:00',
                'values'     => []
            ]
        ]);

        $console->execute($firstUpsertProducts, $destinationPim)->willReturn($firstUpsertProductsResult);
        $firstUpsertProductsResult->getOutput()->willReturn([
            ['status_code' => 201],
            ['status_code' => 201],
        ]);

        $console->execute(new MySqlExecuteCommand(
            'UPDATE destination_database.pim_catalog_product SET created = "2017-09-01 12:45:41" WHERE identifier = "simple_product"'
        ), $destinationPim)->shouldBeCalled();

        $console->execute(new MySqlExecuteCommand(
            'UPDATE destination_database.pim_catalog_product SET created = "2017-09-01 12:48:56" WHERE identifier = "product_with_associations"'
        ), $destinationPim)->shouldBeCalled();

        $secondUpsertProducts = new UpsertListProductsCommand([
            [
                'identifier' => 'product_with_variant_group',
                'family'     => 'clothes',
                'groups'     => ['tshirt_group', 'variant_tshirt'],
                'categories' => ['tshirts'],
                'created'    => '2017-09-01T12:49:21+00:00',
                'values'     => []
            ]
        ]);

        $console->execute($secondUpsertProducts, $destinationPim)->willReturn($secondUpsertProductsResult);
        $secondUpsertProductsResult->getOutput()->willReturn([['status_code' => 201]]);

        $console->execute(new MySqlExecuteCommand(
            'UPDATE destination_database.pim_catalog_product SET created = "2017-09-01 12:49:21" WHERE identifier = "product_with_variant_group"'
        ), $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    private function getSourceProducts()
    {
        return $products = [
            [
                'identifier'    => 'simple_product',
                'family'        => 'clothes',
                'groups'        => ['tshirt_group'],
                'variant_group' => null,
                'categories'    => ['tshirts'],
                'created'       => '2017-09-01T12:45:41+00:00',
                'values'        => [
                    'name' => [
                        'locale' => null,
                        'sope'   => null,
                        'data'   => 'Simple product'
                    ]
                ]
            ],
            [
                'identifier'    => 'product_with_associations',
                'family'        => 'clothes',
                'groups'        => [],
                'variant_group' => null,
                'categories'    => ['tshirts'],
                'created'       => '2017-09-01T12:48:56+00:00',
                'values'        => [],
                'associations'  => ['simple_product', 'product_with_variant_group']
            ],
            [
                'identifier'    => 'product_with_variant_group',
                'family'        => 'clothes',
                'groups'        => ['tshirt_group'],
                'variant_group' => 'variant_tshirt',
                'categories'    => ['tshirts'],
                'created'       => '2017-09-01T12:49:21+00:00',
                'values'        => [],
                'associations'  => []
            ],
        ];
    }
}
