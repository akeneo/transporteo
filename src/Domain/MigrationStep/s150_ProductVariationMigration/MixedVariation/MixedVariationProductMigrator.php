<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\ProductVariantTransformer as InnerVariationProductVariantTransformer;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductVariantTransformer as VariantGroupProductVariantTransformer;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Migrates products according to the migration of mixed variant group and IVB.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationProductMigrator
{
    /** @var ProductModelBuilder */
    private $productModelBuilder;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ProductModelRepository */
    private $productModelRepository;

    /** @var InnerVariationProductVariantTransformer */
    private $innerVariationProductVariantTransformer;

    /** @var VariantGroupProductVariantTransformer */
    private $variantGroupProductVariantTransformer;

    /** @var ProductModelSaver */
    private $productModelSaver;

    public function __construct(
        ProductModelBuilder $productModelBuilder,
        ProductRepository $productRepository,
        ProductModelRepository $productModelRepository,
        ProductModelSaver $productModelSaver,
        InnerVariationProductVariantTransformer $innerVariationProductVariantTransformer,
        VariantGroupProductVariantTransformer $variantGroupProductVariantTransformer
    )
    {
        $this->productModelBuilder = $productModelBuilder;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->innerVariationProductVariantTransformer = $innerVariationProductVariantTransformer;
        $this->variantGroupProductVariantTransformer = $variantGroupProductVariantTransformer;
        $this->productModelSaver = $productModelSaver;
    }

    /**
     * Migrates products variations by creating products models and transforming products in product variants.
     *  - A root product model is created for each variant group of a family an variant axes combination.
     *  - A sub product model is created for each product grouped in the variant group, having variation via an inner-variation-type.
     *  - Each product variation of the product grouped in the variant group is transformed to a product variant having the sub product model as parent.
     *  - Each product grouped in the variant group that doesn't have variation is transformed to a product variant having the root product model as parent.
     */
    public function migrateProducts(MixedVariation $mixedVariation, FamilyVariant $familyVariant, DestinationPim $pim): void
    {
        $variantGroupCombination = $mixedVariation->getVariantGroupCombination();
        $innerVariationType = $mixedVariation->getInnerVariationType();

        foreach ($variantGroupCombination->getGroups() as $variantGroupCode) {
            $rootProductModel = $this->productModelBuilder->buildRootProductModel($variantGroupCode, $familyVariant, $pim);
            $rootProductModel = $this->productModelRepository->persist($rootProductModel, $pim);

            $productsHavingVariants = $mixedVariation->getVariantGroupProductsHavingVariants($variantGroupCode);

            foreach ($productsHavingVariants as $parentProduct) {
                $subProductModel = $this->productModelBuilder->buildSubProductModel($rootProductModel, $parentProduct, $familyVariant, $pim);
                $subProductModel = $this->productModelSaver->save($subProductModel, $pim);

                $this->innerVariationProductVariantTransformer->transform(
                    $subProductModel,
                    $familyVariant,
                    $variantGroupCombination->getFamily(),
                    $innerVariationType->getVariationFamily(),
                    $pim
                );

                $this->productRepository->delete($parentProduct->getIdentifier(), $pim);
            }

            // To transform into variants the remaining products that would not have variations via the IVB.
            $this->variantGroupProductVariantTransformer->transformFromProductModel($rootProductModel, $familyVariant, $pim);
        }
    }
}
