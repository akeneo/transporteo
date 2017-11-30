<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\ProductVariantTransformer as InnerVariationProductVariantTransformer;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariation;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\ProductModelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\ProductModelSaver;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductVariantTransformer as VariantGroupProductVariantTransformer;
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
        ProductModelSaver $productModelSaver,
        InnerVariationProductVariantTransformer $innerVariationProductVariantTransformer,
        VariantGroupProductVariantTransformer $variantGroupProductVariantTransformer
    )
    {
        $this->beConstructedWith($productModelBuilder, $productRepository, $productModelRepository, $productModelSaver, $innerVariationProductVariantTransformer, $variantGroupProductVariantTransformer);
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
        $productModelSaver,
        $innerVariationProductVariantTransformer,
        $variantGroupProductVariantTransformer
    )
    {
        $parentFamily = new Family(20, 'a_family', []);
        $variationFamily = new Family(31, 'ivt_family', []);

        $familyVariant = new FamilyVariant(
            42,
            'family_variant',
            'a_family',
            ['vg_axe_1', 'vg_axe_2'],
            ['ivt_axe'],
            ['family_parent_att_1'],
            ['variation_family_att_1'],
            []
        );

        $variantGroupCombination = new VariantGroupCombination($parentFamily, ['axe_1'], ['variant_group_1', 'variant_group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axe_2']);

        $firstVariantGroup = new VariantGroup('variant_group_1', 1, 1);
        $secondVariantGroup = new VariantGroup('variant_group_2', 1, 1);

        $firstProduct = new Product(11, 'product_1', 21, '2017-06-23 09:13:54', 'variant_group_1');
        $secondProduct = new Product(12, 'product_2', 21, '2017-06-22 09:13:54', 'variant_group_1');
        $thirdProduct = new Product(13, 'product_3', 21, '2017-06-22 09:13:54', 'variant_group_2');

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            [$firstProduct, $secondProduct, $thirdProduct],
            new \ArrayObject([$firstVariantGroup, $secondVariantGroup])
        );

        $productModelBuilder->buildRootProductModel('variant_group_1', $familyVariant, $pim)->willReturn($firstRootProductModel);
        $productModelRepository->persist($firstRootProductModel, $pim)->willReturn($firstRootProductModel);

        $productModelBuilder->buildRootProductModel('variant_group_2', $familyVariant, $pim)->willReturn($secondRootProductModel);
        $productModelRepository->persist($secondRootProductModel, $pim)->willReturn($secondRootProductModel);

        $productModelBuilder->buildSubProductModel($firstRootProductModel, $firstProduct, $familyVariant, $pim)->willReturn($firstSubProductModel);
        $productModelSaver->save($firstSubProductModel, $pim)->willReturn($firstSubProductModel);

        $innerVariationProductVariantTransformer
            ->transform($firstSubProductModel, $familyVariant, $parentFamily, $variationFamily, $pim)
            ->shouldBeCalled();

        $productRepository->delete('product_1', $pim)->shouldBeCalled();

        $productModelBuilder->buildSubProductModel($firstRootProductModel, $secondProduct, $familyVariant, $pim)->willReturn($secondSubProductModel);
        $productModelSaver->save($secondSubProductModel, $pim)->willReturn($secondSubProductModel);

        $innerVariationProductVariantTransformer
            ->transform($secondSubProductModel, $familyVariant, $parentFamily, $variationFamily, $pim)
            ->shouldBeCalled();

        $productRepository->delete('product_2', $pim)->shouldBeCalled();

        $productModelBuilder->buildSubProductModel($secondRootProductModel, $thirdProduct, $familyVariant, $pim)->willReturn($thirdSubProdutModel);
        $productModelSaver->save($thirdSubProdutModel, $pim)->willReturn($thirdSubProdutModel);

        $innerVariationProductVariantTransformer
            ->transform($thirdSubProdutModel, $familyVariant, $parentFamily, $variationFamily, $pim)
            ->shouldBeCalled();

        $productRepository->delete('product_3', $pim)->shouldBeCalled();

        $variantGroupProductVariantTransformer->transformFromProductModel($firstRootProductModel, $familyVariant, $pim)->shouldBeCalled();
        $variantGroupProductVariantTransformer->transformFromProductModel($secondRootProductModel, $familyVariant, $pim)->shouldBeCalled();

        $this->migrateProducts($mixedVariation, $familyVariant, $pim);
    }
}
