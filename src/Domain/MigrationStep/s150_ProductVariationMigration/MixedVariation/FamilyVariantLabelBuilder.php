<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Builds the labels of a family variant for a mixed IVB + variation-group product variation.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantLabelBuilder
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    public function __construct(VariantGroupRepository $variantGroupRepository, InnerVariationTypeRepository $innerVariationTypeRepository)
    {
        $this->variantGroupRepository = $variantGroupRepository;
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
    }

    /**
     * Family variant labels = Labels of the family + Labels of the variant axes attributes + Labels of the inner variation type, separated by a space.
     */
    public function build(VariantGroupCombination $variantGroupCombination,  InnerVariationType $innerVariationType, DestinationPim $pim)
    {
        $familyVariantLabels = $variantGroupCombination->getFamily()->getStandardData()['labels'];

        foreach ($variantGroupCombination->getAxes() as $axe) {
            $axeData = $this->variantGroupRepository->retrieveAttributeData($axe, $pim);
            $axeLabels = $axeData['labels'];

            foreach (array_keys($familyVariantLabels) as $locale) {
                if (isset($axeLabels[$locale])) {
                    $familyVariantLabels[$locale] .= ' '.$axeLabels[$locale];
                }
            }
        }

        foreach ($familyVariantLabels as $locale => $familyVariantLabel) {
            $innerVariationLabel = $this->innerVariationTypeRepository->getLabel($innerVariationType, $locale, $pim);
            if('' !== $innerVariationLabel) {
                $familyVariantLabels[$locale] .= ' '.$innerVariationLabel;
            }
        }

        return $familyVariantLabels;
    }
}
