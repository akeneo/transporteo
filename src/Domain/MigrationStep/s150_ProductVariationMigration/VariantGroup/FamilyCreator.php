<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
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
    /** @var FamilyRepository */
    private $familyRepository;

    /** @var FamilyVariantLabelBuilder */
    private $familyVariantLabelBuilder;

    public function __construct(FamilyRepository $familyRepository, FamilyVariantLabelBuilder $familyVariantLabelBuilder)
    {
        $this->familyRepository = $familyRepository;
        $this->familyVariantLabelBuilder = $familyVariantLabelBuilder;
    }

    public function createFamilyVariant(VariantGroupCombination $variantGroupCombination, Pim $pim): FamilyVariant
    {
        $family = $variantGroupCombination->getFamily();
        $familyVariantCode = $variantGroupCombination->getFamilyVariantCode();

        $variantAxes = $variantGroupCombination->getAxes();
        $variantAttributes = array_diff($family->getAttributes(), $variantGroupCombination->getAttributes(), $variantAxes);

        $familyVariantLabels = $this->familyVariantLabelBuilder->buildFromVariantGroupCombination($variantGroupCombination, $pim);

        $familyVariant = new FamilyVariant(
            null,
            $familyVariantCode,
            $family->getCode(),
            $variantAxes,
            [],
            $variantAttributes,
            [],
            $familyVariantLabels
        );

        $this->familyRepository->persistFamilyVariant($familyVariant, $pim);

        $familyVariantId = $this->familyRepository->retrieveFamilyVariantId($familyVariantCode, $pim);

        if (null === $familyVariantId) {
            throw new ProductVariationMigrationException(sprintf('Unable to retrieve the family variant %s. It seems that its creation failed.', $familyVariantCode));
        }

        return new FamilyVariant(
            $familyVariantId,
            $familyVariant->getCode(),
            $family->getCode(),
            $familyVariant->getLevelOneAxes(),
            $familyVariant->getLevelTwoAxes(),
            $familyVariant->getLevelOneAttributes(),
            $familyVariant->getLevelTwoAttributes(),
            $familyVariant->getLabels()
        );
    }
}
