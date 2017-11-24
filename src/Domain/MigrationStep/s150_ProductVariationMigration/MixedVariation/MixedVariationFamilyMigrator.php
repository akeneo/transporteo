<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\Command\Api\CreateFamilyVariantCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Creates families according to the migration of mixed variant group and IVB.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationFamilyMigrator
{
    /** @var ChainedConsole */
    private $console;

    /** @var FamilyRepository */
    private $familyRepository;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var InnerVariationFamilyMigrator */
    private $innerVariationFamilyMigrator;

    /** @var FamilyVariantRepository */
    private $familyVariantRepository;

    public function __construct(
        ChainedConsole $console,
        FamilyRepository $familyRepository,
        VariantGroupRepository $variantGroupRepository,
        InnerVariationTypeRepository $innerVariationTypeRepository,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        FamilyVariantRepository $familyVariantRepository
    ) {
        $this->console = $console;
        $this->familyRepository = $familyRepository;
        $this->variantGroupRepository = $variantGroupRepository;
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->innerVariationFamilyMigrator = $innerVariationFamilyMigrator;
        $this->familyVariantRepository = $familyVariantRepository;
    }

    public function migrateFamilyVariant(VariantGroupCombination $variantGroupCombination, InnerVariationType $innerVariationType, DestinationPim $pim): FamilyVariant
    {
        $family = $this->familyRepository->findByCode($variantGroupCombination->getFamily()->getCode(), $pim);
        $familyData = $family->getStandardData();

        $innerVariationFamily = $this->familyRepository->findById($innerVariationType->getVariationFamilyId(), $pim);
        $innerVariationFamilyData = $innerVariationFamily->getStandardData();

        $this->innerVariationFamilyMigrator->migrateFamilyAttributes($family, $innerVariationFamily, $pim);

        $familyVariantCode = $variantGroupCombination->getFamily()->getCode();
        $variantGroupAttributes = $this->variantGroupRepository->retrieveGroupAttributes($variantGroupCombination->getGroups()[0], $pim);
        $secondaryVariantAttributes = array_diff($innerVariationFamilyData['attributes'], $innerVariationType->getAxesCodes(), ['variation_parent_product']);
        $primaryVariantAttributes = array_diff($familyData['attributes'], $variantGroupAttributes, $variantGroupCombination->getAxes(), $secondaryVariantAttributes);

        $familyVariantData = [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'attributes' => $primaryVariantAttributes,
                    'axes' => $variantGroupCombination->getAxes()
                ],
                [
                    'level' => 2,
                    'attributes' => $secondaryVariantAttributes,
                    'axes' => $innerVariationType->getAxesCodes()
                ]
            ],
            'labels' => $this->buildFamilyVariantLabels($familyData, $variantGroupCombination, $innerVariationType, $pim)
        ];

        $this->console->execute(new CreateFamilyVariantCommand($family->getCode(), $familyVariantCode, $familyVariantData), $pim);

        $familyVariantId = $this->familyVariantRepository->getFamilyVariantId($familyVariantCode, $pim);

        if (null === $familyVariantId) {
            throw new ProductVariationMigrationException(sprintf('Unable to retrieve the family variant %s. It seems that its creation failed.', $familyVariantCode));
        }

        return new FamilyVariant($familyVariantId, $familyVariantCode, $family->getCode(), $primaryVariantAttributes, $variantGroupAttributes);
    }

    /**
     * Family variant labels = Labels of the family + Labels of the variant axes attributes + Labels of the inner variation type, separated by a space.
     */
    private function buildFamilyVariantLabels(array $familyData, VariantGroupCombination $variantGroupCombination,  InnerVariationType $innerVariationType, DestinationPim $pim): array
    {
        $familyVariantLabels = $familyData['labels'];

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
