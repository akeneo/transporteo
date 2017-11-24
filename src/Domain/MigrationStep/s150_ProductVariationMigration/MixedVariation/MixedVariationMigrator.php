<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migration for products having variations through variant-group and IVB both.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationMigrator
{
    /** @var MixedVariationFamilyMigrator */
    private $familyMigrator;

    /** @var MixedVariationProductMigrator */
    private $productMigrator;

    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;
    /** @var ProductRepository */
    private $productRepository;

    public function __construct(
        MixedVariationFamilyMigrator $familyMigrator,
        MixedVariationProductMigrator $productMigrator,
        InnerVariationTypeRepository $innerVariationTypeRepository,
        ProductRepository $productRepository
    ) {
        $this->familyMigrator = $familyMigrator;
        $this->productMigrator = $productMigrator;
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->productRepository = $productRepository;
    }

    public function migrate(VariantGroupCombination $variantGroupCombination, SourcePim $sourcePim, DestinationPim $destinationPim): bool
    {
        if (!$sourcePim->hasIvb()) {
            return false;
        }

        $innerVariationType = $this->innerVariationTypeRepository->findOneForFamilyCode($variantGroupCombination->getFamily()->getCode(), $destinationPim);

        if (null === $innerVariationType) {
            return false;
        }

        $productsHavingVariants = $this->productRepository->findAllHavingVariantsByGroups($variantGroupCombination->getGroups(), $innerVariationType->getVariationFamilyId(), $destinationPim);

        if (empty($productsHavingVariants)) {
            return false;
        }

        $familyVariant = $this->familyMigrator->migrateFamilyVariant($variantGroupCombination, $innerVariationType, $destinationPim);

        $this->productMigrator->migrateLevelOneProductModels($variantGroupCombination, $destinationPim);

        $productModels = $this->productMigrator->migrateLevelTwoProductModels($productsHavingVariants, $variantGroupCombination, $destinationPim);

        foreach ($productModels as $productModel) {
            $this->productMigrator->migrateInnerVariationTypeProductVariants($productModel, $familyVariant, $innerVariationType, $destinationPim);
        }

        $this->productMigrator->migrateRemainingProductVariants($familyVariant, $variantGroupCombination, $destinationPim);

        return true;
    }
}
