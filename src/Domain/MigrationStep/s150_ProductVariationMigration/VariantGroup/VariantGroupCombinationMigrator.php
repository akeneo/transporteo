<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Migrates product variations according to a product model combination.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombinationMigrator
{
    /** @var ProductModelRepository */
    private $productModelRepository;

    /** @var ProductModelBuilder */
    private $productModelBuilder;

    /** @var ProductVariantTransformer */
    private $productVariantTransformer;

    /** @var FamilyVariantRepository */
    private $familyVariantRepository;

    /** @var FamilyVariantBuilder */
    private $familyVariantBuilder;

    public function __construct(
        ProductModelRepository $productModelRepository,
        ProductModelBuilder $productModelBuilder,
        ProductVariantTransformer $productVariantTransformer,
        FamilyVariantRepository $familyVariantRepository,
        FamilyVariantBuilder $familyVariantBuilder
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelBuilder = $productModelBuilder;
        $this->productVariantTransformer = $productVariantTransformer;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->familyVariantBuilder = $familyVariantBuilder;
    }

    public function migrate(VariantGroupCombination $variantGroupCombination, DestinationPim $pim): void
    {
        $familyVariant = $this->familyVariantBuilder->buildFromVariantGroupCombination($variantGroupCombination, $pim);
        $familyVariant = $this->familyVariantRepository->persist($familyVariant, $pim);

        foreach ($variantGroupCombination->getGroups() as $variantGroupCode) {
            $productModel = $this->productModelBuilder->buildFromVariantGroup($variantGroupCode, $familyVariant, $pim);
            $productModel = $this->productModelRepository->persist($productModel, $pim);

            $this->productVariantTransformer->transformFromProductModel($productModel, $familyVariant, $pim);
        }
    }
}
