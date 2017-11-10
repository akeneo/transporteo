<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Creates families according to the migration of variant groups.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantBuilder
{
    /** @var FamilyVariantLabelBuilder */
    private $familyVariantLabelBuilder;

    /** @var FamilyVariantCodeBuilder */
    private $familyVariantCodeBuilder;

    public function __construct(
        FamilyVariantLabelBuilder $familyVariantLabelBuilder,
        FamilyVariantCodeBuilder $familyVariantCodeBuilder
    ) {
        $this->familyVariantLabelBuilder = $familyVariantLabelBuilder;
        $this->familyVariantCodeBuilder = $familyVariantCodeBuilder;
    }

    public function buildFromVariantGroupCombination(VariantGroupCombination $variantGroupCombination, Pim $pim): FamilyVariant
    {
        $family = $variantGroupCombination->getFamily();
        $familyVariantCode = $this->familyVariantCodeBuilder->buildFromVariantGroupCombination($variantGroupCombination);

        $variantAxes = $variantGroupCombination->getAxes();
        $variantAttributes = array_diff($family->getAttributes(), $variantGroupCombination->getAttributes(), $variantAxes);

        $familyVariantLabels = $this->familyVariantLabelBuilder->buildFromVariantGroupCombination($variantGroupCombination, $pim);

        return new FamilyVariant(
            null,
            $familyVariantCode,
            $family->getCode(),
            $variantAxes,
            [],
            $variantAttributes,
            [],
            $familyVariantLabels
        );
    }
}
