<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\ProductModelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelBuilderSpec extends ObjectBehavior
{
    public function let(VariantGroupRepository $variantGroupRepository, ProductRepository $productRepository)
    {
        $this->beConstructedWith($variantGroupRepository, $productRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelBuilder::class);
    }

    public function it_builds_a_root_product_model(DestinationPim $pim, $variantGroupRepository)
    {
        $familyVariant = new FamilyVariant(null, 'family_variant');
        $variantGroupRepository->retrieveVariantGroupCategories('vg_1', $pim)->willReturn(['cat_1', 'cat_2']);
        $variantGroupRepository->retrieveGroupAttributeValues('vg_1', $pim)->willReturn([
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
        ]);

        $productModel = new ProductModel(
            null,
            'vg_1',
            'family_variant',
            ['cat_1', 'cat_2'],
            [
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
        );

        $this->buildRootProductModel('vg_1', $familyVariant, $pim)->shouldBeLike($productModel);
    }

    public function it_builds_a_sub_product_model(DestinationPim $pim, $productRepository)
    {
        $parentProduct = new Product(42, 'product_1', null, null, null);
        $familyVariant = new FamilyVariant(null, 'family_variant');
        $parentProductModel = new ProductModel(51, 'parent_product_model', 'family_variant', [], []);

        $productRepository->getStandardData('product_1', $pim)->willReturn([
            'identifier' => 'product_1',
            'categories' => ['cat_1', 'cat2'],
            'values' => [
                'name' => [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'Product 1'
                ]
            ]
        ]);

        $productModel = new ProductModel(
            null,
            'product_1',
            'family_variant',
            ['cat_1', 'cat2'],
            [
                'name' => [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'Product 1'
                ]
            ],
            'parent_product_model'
        );

        $this->buildSubProductModel($parentProductModel, $parentProduct, $familyVariant, $pim)->shouldBeLike($productModel);
    }
}
