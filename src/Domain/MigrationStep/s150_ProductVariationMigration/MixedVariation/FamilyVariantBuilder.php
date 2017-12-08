<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantCodeBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Builds family variant for a mixed IVB + variation-group product variation.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantBuilder
{
    /** @var  FamilyVariantLabelBuilder */
    private $familyLabelBuilder;

    /** @var InnerVariationFamilyMigrator */
    private $innerVariationFamilyMigrator;

    /** @var FamilyVariantCodeBuilder */
    private $familyVariantCodeBuilder;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    public function __construct(
        FamilyVariantLabelBuilder $familyLabelBuilder,
        FamilyVariantCodeBuilder $familyVariantCodeBuilder,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        VariantGroupRepository $variantGroupRepository
    ){
        $this->familyLabelBuilder = $familyLabelBuilder;
        $this->familyVariantCodeBuilder = $familyVariantCodeBuilder;
        $this->innerVariationFamilyMigrator = $innerVariationFamilyMigrator;
        $this->variantGroupRepository = $variantGroupRepository;
    }

    public function build(MixedVariation $mixedVariation, DestinationPim $pim)
    {
        $variantGroupCombination = $mixedVariation->getVariantGroupCombination();
        $innerVariationType = $mixedVariation->getInnerVariationType();
        $family = $variantGroupCombination->getFamily();
        $familyData = $family->getStandardData();
        $innerVariationFamily = $innerVariationType->getVariationFamily();
        $innerVariationFamilyData = $innerVariationFamily->getStandardData();

        $this->innerVariationFamilyMigrator->migrateFamilyAttributes($family, $innerVariationFamily, $pim);

        $levelOneAxes = $variantGroupCombination->getAxes();
        $levelTwoAxes = $innerVariationType->getAxesCodes();

        $variantGroupAttributes = $this->variantGroupRepository->retrieveGroupAttributes($variantGroupCombination->getGroups()[0], $pim);
        $levelTwoAttributes = array_diff($innerVariationFamilyData['attributes'], $levelTwoAxes, ['variation_parent_product']);
        $levelOneAttributes = array_diff($familyData['attributes'], $variantGroupAttributes, $levelOneAxes, $levelTwoAxes, $levelTwoAttributes);

        $code = $this->familyVariantCodeBuilder->buildFromVariantGroupCombination($variantGroupCombination);
        $labels = $this->familyLabelBuilder->build($variantGroupCombination, $innerVariationType, $pim);

        return new FamilyVariant(
            null,
            $code,
            $family->getCode(),
            $levelOneAxes,
            $levelTwoAxes,
            $levelOneAttributes,
            $levelTwoAttributes,
            $labels
        );
    }
}
