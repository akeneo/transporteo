<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\ProductVariantTransformer;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariation;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\ProductModelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationProductMigratorSpec extends ObjectBehavior
{
    public function let(
        ProductModelBuilder $productModelBuilder,
        ProductRepository $productRepository,
        ProductModelRepository $productModelRepository,
        ProductVariantTransformer $productVariantTransformer
    )
    {
        $this->beConstructedWith($productModelBuilder, $productRepository, $productModelRepository, $productVariantTransformer);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationProductMigrator::class);
    }

    public function it_migrates_products(
        DestinationPim $pim,
        ProductModel $firstRootProductModel,
        ProductModel $secondRootProductModel,
        ProductModel $firstSubProductModel,
        ProductModel $secondSubProductModel,
        ProductModel $thirdSubProdutModel,
        $productModelBuilder,
        $productRepository,
        $productModelRepository,
        $productVariantTransformer
    )
    {
        $parentFamily = new Family(20, 'a_family', []);
        $variationFamily = new Family(31, 'ivt_family', []);

        $familyVariant = new FamilyVariant(
            42,
            'family_variant',
            'a_family',
            ['vg_axis_1', 'vg_axis_2'],
            ['ivt_axis'],
            ['family_parent_att_1'],
            ['variation_family_att_1'],
            []
        );

        $variantGroupCombination = new VariantGroupCombination($parentFamily, ['axis_1'], ['variant_group_1', 'variant_group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $firstVariantGroup = new VariantGroup('variant_group_1', 1, 1);
        $secondVariantGroup = new VariantGroup('variant_group_2', 1, 1);

        $firstProduct = new Product(11, 'product_1', 21, '2017-06-23 09:13:54', 'variant_group_1');
        $secondProduct = new Product(12, 'product_2', 21, '2017-06-22 09:13:54', 'variant_group_1');
        $thirdProduct = new Product(13, 'product_3', 21, '2017-06-22 09:13:54', 'variant_group_2');

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            new \ArrayObject([$firstVariantGroup, $secondVariantGroup])
        );

        $productModelBuilder->buildRootProductModel('variant_group_1', $familyVariant, $pim)->willReturn($firstRootProductModel);
        $productModelRepository->persist($firstRootProductModel, $pim)->willReturn($firstRootProductModel);

        $productModelBuilder->buildRootProductModel('variant_group_2', $familyVariant, $pim)->willReturn($secondRootProductModel);
        $productModelRepository->persist($secondRootProductModel, $pim)->willReturn($secondRootProductModel);

        $productRepository->findAllByGroupCode('variant_group_1', $pim)->willReturn([$firstProduct, $secondProduct]);
        $productRepository->findAllByGroupCode('variant_group_2', $pim)->willReturn([$thirdProduct]);

        $productModelBuilder->buildSubProductModel($firstRootProductModel, $firstProduct, $familyVariant, $pim)->willReturn($firstSubProductModel);
        $productModelRepository->persist($firstSubProductModel, $pim)->willReturn($firstSubProductModel);

        $productVariantTransformer
            ->transform($firstSubProductModel, $familyVariant, $parentFamily, $variationFamily, $pim)
            ->shouldBeCalled();

        $productRepository->delete('product_1', $pim)->shouldBeCalled();

        $productModelBuilder->buildSubProductModel($firstRootProductModel, $secondProduct, $familyVariant, $pim)->willReturn($secondSubProductModel);
        $productModelRepository->persist($secondSubProductModel, $pim)->willReturn($secondSubProductModel);

        $productVariantTransformer
            ->transform($secondSubProductModel, $familyVariant, $parentFamily, $variationFamily, $pim)
            ->shouldBeCalled();

        $productRepository->delete('product_2', $pim)->shouldBeCalled();

        $productModelBuilder->buildSubProductModel($secondRootProductModel, $thirdProduct, $familyVariant, $pim)->willReturn($thirdSubProdutModel);
        $productModelRepository->persist($thirdSubProdutModel, $pim)->willReturn($thirdSubProdutModel);

        $productVariantTransformer
            ->transform($thirdSubProdutModel, $familyVariant, $parentFamily, $variationFamily, $pim)
            ->shouldBeCalled();

        $productRepository->delete('product_3', $pim)->shouldBeCalled();

        $this->migrateProducts($mixedVariation, $familyVariant, $pim);
    }
}
