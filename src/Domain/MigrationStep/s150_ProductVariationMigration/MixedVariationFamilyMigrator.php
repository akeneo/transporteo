<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\CreateFamilyVariantCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
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

    /** @var MixedVariationRetriever */
    private $mixedVariationRetriever;

    /** @var VariantGroupRetriever */
    private $variantGroupRetriever;

    /** @var InnerVariationRetriever */
    private $innerVariationRetriever;

    /** @var InnerVariationFamilyMigrator */
    private $innerVariationFamilyMigrator;

    public function __construct(
        ChainedConsole $console,
        MixedVariationRetriever $mixedVariationRetriever,
        VariantGroupRetriever $variantGroupRetriever,
        InnerVariationRetriever $innerVariationRetriever,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator
    ) {
        $this->console = $console;
        $this->mixedVariationRetriever = $mixedVariationRetriever;
        $this->variantGroupRetriever = $variantGroupRetriever;
        $this->innerVariationRetriever = $innerVariationRetriever;
        $this->innerVariationFamilyMigrator = $innerVariationFamilyMigrator;
    }

    public function migrateFamilyVariant(VariantGroupCombination $variantGroupCombination, InnerVariationType $innerVariationType, DestinationPim $pim): FamilyVariant
    {
        $family = $this->mixedVariationRetriever->retrieveFamilyByCode($variantGroupCombination->getFamilyCode(), $pim);
        $familyData = $family->getStandardData();

        $innerVariationFamily = $this->mixedVariationRetriever->retrieveFamilyById($innerVariationType->getVariationFamilyId(), $pim);
        $innerVariationFamilyData = $innerVariationFamily->getStandardData();

        $this->innerVariationFamilyMigrator->migrateFamilyAttributes($family, $innerVariationFamily, $pim);

        $familyVariantCode = $variantGroupCombination->getFamilyVariantCode();
        $variantGroupAttributes = $this->variantGroupRetriever->retrieveGroupAttributes($variantGroupCombination->getGroups()[0], $pim);
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

        $familyVariantId = $this->variantGroupRetriever->retrieveFamilyVariantId($familyVariantCode, $pim);

        if (null === $familyVariantId) {
            throw new ProductVariationMigrationException(sprintf('Unable to retrieve the family variant %s. It seems that its creation failed.', $familyVariantCode));
        }

        return new FamilyVariant($familyVariantId, $familyVariantCode, $primaryVariantAttributes, $variantGroupAttributes);
    }

    /**
     * Family variant labels = Labels of the family + Labels of the variant axes attributes + Labels of the inner variation type, separated by a space.
     */
    private function buildFamilyVariantLabels(array $familyData, VariantGroupCombination $variantGroupCombination,  InnerVariationType $innerVariationType, DestinationPim $pim): array
    {
        $familyVariantLabels = $familyData['labels'];

        foreach ($variantGroupCombination->getAxes() as $axe) {
            $axeData = $this->variantGroupRetriever->retrieveAttributeData($axe, $pim);
            $axeLabels = $axeData['labels'];

            foreach (array_keys($familyVariantLabels) as $locale) {
                if (isset($axeLabels[$locale])) {
                    $familyVariantLabels[$locale] .= ' '.$axeLabels[$locale];
                }
            }
        }

        foreach ($familyVariantLabels as $locale => $familyVariantLabel) {
            $innerVariationLabel = $this->innerVariationRetriever->retrieveInnerVariationLabel($innerVariationType, $locale, $pim);
            if('' !== $innerVariationLabel) {
                $familyVariantLabels[$locale] .= ' '.$innerVariationLabel;
            }
        }

        return $familyVariantLabels;
    }
}
