<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Creates families according to the migration of variant groups.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyCreator
{
    /** @var FamilyVariantImporter */
    private $familyVariantImporter;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var FamilyVariantLabelBuilder */
    private $familyVariantLabelBuilder;

    public function __construct(FamilyVariantImporter $familyVariantImporter, VariantGroupRepository $variantGroupRepository, FamilyVariantLabelBuilder $familyVariantLabelBuilder)
    {
        $this->familyVariantImporter = $familyVariantImporter;
        $this->variantGroupRepository = $variantGroupRepository;
        $this->familyVariantLabelBuilder = $familyVariantLabelBuilder;
    }

    public function createFamilyVariant(VariantGroupCombination $variantGroupCombination, Pim $pim): FamilyVariant
    {
        $familyCode = $variantGroupCombination->getFamilyCode();
        $familyVariantCode = $variantGroupCombination->getFamilyVariantCode();
        $familyData = $this->variantGroupRepository->retrieveFamilyData($familyCode, $pim);

        $variantAxes = $variantGroupCombination->getAxes();
        $variantGroupAttributes = $this->variantGroupRepository->retrieveGroupAttributes($variantGroupCombination->getGroups()[0], $pim);
        $variantAttributes = array_diff($familyData['attributes'], $variantGroupAttributes, $variantAxes);

        $familyVariant = [
            'code' => $familyVariantCode,
            'family' => $familyCode,
            'variant-axes_1' => implode(',', $variantAxes),
            'variant-axes_2' => '',
            'variant-attributes_1' => implode(',', $variantAttributes),
            'variant-attributes_2' => '',
        ];

        $familyVariantLabels = $this->familyVariantLabelBuilder->buildFromVariantGroupCombination($familyData, $variantGroupCombination, $pim);

        foreach ($familyVariantLabels as $locale => $label) {
            $familyVariant['label-'.$locale] = $label;
        }

        $this->familyVariantImporter->import([$familyVariant], $pim);

        $familyVariantId = $this->variantGroupRepository->retrieveFamilyVariantId($familyVariantCode, $pim);

        if (null === $familyVariantId) {
            throw new ProductVariationMigrationException(sprintf('Unable to retrieve the family variant %s. It seems that its creation failed.', $familyVariantCode));
        }

        return new FamilyVariant($familyVariantId, $familyVariantCode, $variantAttributes, $variantGroupAttributes);
    }
}
