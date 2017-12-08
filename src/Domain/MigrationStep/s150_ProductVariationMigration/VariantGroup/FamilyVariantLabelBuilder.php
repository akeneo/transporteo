<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Builds the labels of a family variant.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantLabelBuilder
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    public function __construct(VariantGroupRepository $variantGroupRepository)
    {
        $this->variantGroupRepository = $variantGroupRepository;
    }

    public function buildFromVariantGroupCombination(VariantGroupCombination $variantGroupCombination, Pim $pim): array
    {
        $familyVariantLabels = $variantGroupCombination->getFamily()->getLabels();

        foreach ($variantGroupCombination->getAxes() as $axis) {
            $axisData = $this->variantGroupRepository->retrieveAttributeData($axis, $pim);
            $axisLabels = $axisData['labels'];

            foreach (array_keys($familyVariantLabels) as $locale) {
                if (isset($axisLabels[$locale])) {
                    $familyVariantLabels[$locale] .= ' '.$axisLabels[$locale];
                }
            }
        }

        return $familyVariantLabels;
    }
}
