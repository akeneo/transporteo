<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

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
    /** @var MixedVariationRetriever */
    private $mixedVariationRetriever;

    /** @var MixedVariationFamilyMigrator */
    private $familyMigrator;

    /** @var MixedVariationProductMigrator */
    private $productMigrator;

    public function __construct(MixedVariationRetriever $mixedVariationRetriever, MixedVariationFamilyMigrator $familyMigrator, MixedVariationProductMigrator $productMigrator)
    {
        $this->mixedVariationRetriever = $mixedVariationRetriever;
        $this->familyMigrator = $familyMigrator;
        $this->productMigrator = $productMigrator;
    }

    public function migrate(VariantGroupCombination $variantGroupCombination, SourcePim $sourcePim, DestinationPim $destinationPim): bool
    {
        if (!$sourcePim->hasIvb()) {
            return false;
        }

        $innerVariationType = $this->mixedVariationRetriever->retrieveInnerVariationTypeByFamilyCode($variantGroupCombination->getFamilyCode(), $destinationPim);

        if (null === $innerVariationType) {
            return false;
        }

        $productsHavingVariants = $this->mixedVariationRetriever->retrieveProductsHavingVariantsByGroups($variantGroupCombination->getGroups(), $innerVariationType->getVariationFamilyId(), $destinationPim);

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
