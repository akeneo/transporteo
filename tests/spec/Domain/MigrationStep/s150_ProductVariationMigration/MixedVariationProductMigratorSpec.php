<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\CreateProductModelCommand;
use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\Api\GetProductCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupProductMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationProductMigratorSpec extends ObjectBehavior
{
    public function let(
        ChainedConsole $console,
        VariantGroupProductMigrator $variantGroupProductMigrator,
        MixedVariationRetriever $mixedVariationRetriever
    )
    {
        $this->beConstructedWith($console, $variantGroupProductMigrator, $mixedVariationRetriever);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationProductMigrator::class);
    }

    public function it_successfully_migrates_level_two_products_models(
        $console,
        $mixedVariationRetriever,
        Pim $pim,
        CommandResult $commandResult,
        VariantGroupCombination $variantGroupCombination
    ) {
        $parentProducts = [
            new Product(11, 'product_1', 21, '2017-06-23 09:13:54', 'variant_group_1'),
            new Product(12, 'product_2', 21, '2017-06-22 09:13:54', 'variant_group_2'),
        ];

        $parentProductsData = [
            [
                'identifier' => 'product_1',
                'categories' => ['cat_1', 'cat2'],
                'values' => [
                    'name' => [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Product 1'
                    ]
                ]
            ],
            [
                'identifier' => 'product_2',
                'categories' => ['cat_3'],
                'values' => [
                    'name' => [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Product 2'
                    ],
                    'brand' => [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'Dell'
                    ]
                ]
            ]
        ];

        $console->execute(new GetProductCommand('product_1'), $pim)->willReturn($commandResult);
        $console->execute(new GetProductCommand('product_2'), $pim)->willReturn($commandResult);

        $commandResult->getOutput()->willReturn($parentProductsData[0], $parentProductsData[1]);

        $variantGroupCombination->getFamilyVariantCode()->willReturn('family_variant_1');

        $console->execute(new CreateProductModelCommand(
            'product_1',
            [
                'family_variant' => 'family_variant_1',
                'categories' => ['cat_1', 'cat2'],
                'parent' => 'variant_group_1',
                'values' => [
                    'name' => [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Product 1'
                    ]
                ]
            ]
        ), $pim)->shouldBeCalled();

        $console->execute(new CreateProductModelCommand(
            'product_2',
            [
                'family_variant' => 'family_variant_1',
                'categories' => ['cat_3'],
                'parent' => 'variant_group_2',
                'values' => [
                    'name' => [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Product 2'
                    ],
                    'brand' => [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'Dell'
                    ]
                ]
            ]
        ), $pim)->shouldBeCalled();

        $mixedVariationRetriever->retrieveProductModelId('product_1', $pim)->willReturn(41);
        $mixedVariationRetriever->retrieveProductModelId('product_2', $pim)->willReturn(42);

        $console->execute(new DeleteProductCommand('product_1'), $pim)->shouldBeCalled();
        $console->execute(new DeleteProductCommand('product_2'), $pim)->shouldBeCalled();

        $this->migrateLevelTwoProductModels($parentProducts, $variantGroupCombination, $pim);
    }

    public function it_throws_an_exception_if_a_product_model_creation_failed(
        $console,
        $mixedVariationRetriever,
        Pim $pim,
        CommandResult $commandResult,
        VariantGroupCombination $variantGroupCombination
    ) {
        $parentProducts = [
            new Product(11, 'product_1', 21, '2017-06-23 09:13:54', 'variant_group_1'),
        ];

        $parentProductsData = [
            [
                'identifier' => 'product_1',
                'categories' => ['cat_1', 'cat2'],
                'values' => [
                    'name' => [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Product 1'
                    ]
                ]
            ]
        ];

        $console->execute(new GetProductCommand('product_1'), $pim)->willReturn($commandResult);
        $commandResult->getOutput()->willReturn($parentProductsData[0]);
        $variantGroupCombination->getFamilyVariantCode()->willReturn('family_variant_1');

        $console->execute(new CreateProductModelCommand(
            'product_1',
            [
                'family_variant' => 'family_variant_1',
                'categories' => ['cat_1', 'cat2'],
                'parent' => 'variant_group_1',
                'values' => [
                    'name' => [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Product 1'
                    ]
                ]
            ]
        ), $pim)->shouldBeCalled();


        $mixedVariationRetriever->retrieveProductModelId('product_1', $pim)->willReturn(null);


        $this->shouldThrow(new ProductVariationMigrationException('Unable to retrieve the product model product_1. It seems that its creation failed.'))
            ->during('migrateLevelTwoProductModels', [$parentProducts, $variantGroupCombination, $pim]);
    }

    public function it_migrates_inner_variation_type_product_variants(
        FamilyVariant $familyVariant,
        InnerVariationType $innerVariationType,
        DestinationPim $pim,
        $console
    ) {
        $productModel = new ProductModel(11, 'product_model_1', 22);

        $familyVariant->getId()->willReturn(34);
        $innerVariationType->getVariationFamilyId()->willReturn(42);

        $console->execute(new MySqlExecuteCommand(
            'UPDATE pim_catalog_product '
            .' SET family_id = 22, product_model_id = 11, family_variant_id = 34, product_type = "variant_product",'
            .' raw_values = JSON_REMOVE(raw_values, \'$.variation_parent_product\')'
            .' WHERE family_id = 42'
            .' AND JSON_EXTRACT(raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = "product_model_1"'
        ), $pim)->shouldBeCalled();

        $this->migrateInnerVariationTypeProductVariants($productModel, $familyVariant, $innerVariationType, $pim);
    }
}
