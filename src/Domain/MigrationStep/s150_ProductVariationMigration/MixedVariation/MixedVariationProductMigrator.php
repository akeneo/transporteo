<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\ProductVariantTransformer;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
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

    /** @var ProductVariantTransformer */
    private $productVariantTransformer;

    public function __construct(
        ProductModelBuilder $productModelBuilder,
        ProductRepository $productRepository,
        ProductModelRepository $productModelRepository,
        ProductVariantTransformer $productVariantTransformer
    ) {
        $this->productModelBuilder = $productModelBuilder;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->productVariantTransformer = $productVariantTransformer;
    }

    /**
     * Migrates products variations by creating products models and transforming products in product variants.
     *  - A root product model is created for each variant group of a family an variant axes combination.
     *  - A sub product model is created for each product grouped in the variant group.
     *  - Each product variation of the product grouped in the variant group is transformed to a product variant having the sub product model as parent.
     */
    public function migrateProducts(MixedVariation $mixedVariation, FamilyVariant $familyVariant, DestinationPim $pim): void
    {
        $variantGroupCombination = $mixedVariation->getVariantGroupCombination();
        $innerVariationType = $mixedVariation->getInnerVariationType();

        foreach ($variantGroupCombination->getGroups() as $variantGroupCode) {
            $rootProductModel = $this->productModelBuilder->buildRootProductModel($variantGroupCode, $familyVariant, $pim);
            $rootProductModel = $this->productModelRepository->persist($rootProductModel, $pim);

            $parentProducts = $this->productRepository->findAllByGroupCode($variantGroupCode, $pim);

            foreach ($parentProducts as $parentProduct) {
                $subProductModel = $this->productModelBuilder->buildSubProductModel($rootProductModel, $parentProduct, $familyVariant, $pim);
                $subProductModel = $this->productModelRepository->persist($subProductModel, $pim);

                $this->productVariantTransformer->transform(
                    $subProductModel,
                    $familyVariant,
                    $variantGroupCombination->getFamily(),
                    $innerVariationType->getVariationFamily(),
                    $pim
                );

                $this->productRepository->delete($parentProduct->getIdentifier(), $pim);
            }
        }
    }
}
