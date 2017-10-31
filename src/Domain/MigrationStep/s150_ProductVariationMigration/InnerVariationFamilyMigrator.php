<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\UpdateFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Migrates the families data of an InnerVariationType.
 *  - Copy the attributes of the IVB variation family to the parent family
 *  - Create the real families variants.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationFamilyMigrator
{
    /** @var InnerVariationRetriever */
    private $innerVariationRetriever;

    /** @var FamilyVariantImporter */
    private $familyVariantImporter;

    /** @var LoggerInterface */
    private $logger;

    /** @var ChainedConsole */
    private $console;

    public function __construct(
        InnerVariationRetriever $innerVariationRetriever,
        FamilyVariantImporter $familyVariantImporter,
        ChainedConsole $console,
        LoggerInterface $logger
    ) {
        $this->innerVariationRetriever = $innerVariationRetriever;
        $this->familyVariantImporter = $familyVariantImporter;
        $this->logger = $logger;
        $this->console = $console;
    }

    public function migrate(InnerVariationType $innerVariationType, Pim $pim): void
    {
        $innerVariationFamily = $this->innerVariationRetriever->retrieveInnerVariationFamily($innerVariationType, $pim);
        $parentFamilies = $this->innerVariationRetriever->retrieveParentFamiliesHavingProductsWithVariants($innerVariationType, $pim);

        $familiesVariants = [];
        foreach ($parentFamilies as $parentFamily) {
            $this->migrateFamilyAttributes($parentFamily, $innerVariationFamily, $pim);

            $familiesVariants[] = $this->buildFamilyVariant($parentFamily, $innerVariationFamily, $innerVariationType, $pim);
        }

        if (!empty($familiesVariants)) {
            $this->familyVariantImporter->import($familiesVariants, $pim);
        }
    }

    /**
     * Creates a family variant from an InnerVariationType.
     */
    private function buildFamilyVariant(Family $parentFamily, Family $innerVariationFamily, InnerVariationType $innerVariationType, Pim $pim): array
    {
        $axesCodes = [];
        foreach ($innerVariationType->getAxes() as $axe) {
            $axesCodes[] = $axe['code'];
        }

        $parentFamilyData = $parentFamily->getStandardData();
        $innerVariationFamilyData = $innerVariationFamily->getStandardData();
        $attributes = $this->removeVariationParentProductAttribute($innerVariationFamilyData['attributes']);

        $familyVariant = [
            'code' => $parentFamily->getCode().'_'.$innerVariationFamily->getCode(),
            'family' => $parentFamily->getCode(),
            'variant-axes_1' => implode(',', $axesCodes),
            'variant-axes_2' => '',
            'variant-attributes_1' => implode(',', $attributes),
            'variant-attributes_2' => '',
        ];

        foreach ($parentFamilyData['labels'] as $locale => $label) {
            $innerVariationLabel = $this->innerVariationRetriever->retrieveInnerVariationLabel($innerVariationType, $locale, $pim);
            $familyVariant['label-'.$locale] = $label.' '.$innerVariationLabel;
        }

        $this->logger->debug('Create the new family variant '.$familyVariant['code'], $familyVariant);

        return $familyVariant;
    }

    /**
     * Adds the attributes (and their requirements) of a "variant" family into a "parent" family.
     */
    public function migrateFamilyAttributes(Family $parentFamily, Family $familyVariant, Pim $pim): void
    {
        $this->logger->debug(sprintf('Add the attributes of family %s to family %s', $familyVariant->getCode(), $parentFamily->getCode()));

        $parentFamilyData = $parentFamily->getStandardData();
        $familyVariantData = $familyVariant->getStandardData();

        $familyVariantData['attributes'] = $this->removeVariationParentProductAttribute($familyVariantData['attributes']);

        $attributesToAdd = array_diff($familyVariantData['attributes'], $parentFamilyData['attributes']);
        $parentFamilyData['attributes'] = array_merge($parentFamilyData['attributes'], $attributesToAdd);

        foreach (array_keys($familyVariantData['attribute_requirements']) as $channel) {
            $familyVariantRequirements = $this->removeVariationParentProductAttribute($familyVariantData['attribute_requirements'][$channel]);
            $requirementsToAdd = array_diff($familyVariantRequirements, $parentFamilyData['attribute_requirements'][$channel]);
            $parentFamilyData['attribute_requirements'][$channel] = array_merge($parentFamilyData['attribute_requirements'][$channel], $requirementsToAdd);
        }

        try {
            $this->console->execute(new UpdateFamilyCommand($parentFamilyData), $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to migrate the attributes of the variant family %s into the parent family %s : %s',
                $familyVariant->getCode(),
                $parentFamily->getCode(),
                $exception->getMessage()
            ));
        }
    }

    /**
     * Removes the attribute variation_parent_product from a list of attributes.
     */
    private function removeVariationParentProductAttribute(array $attributes): array
    {
        return array_diff($attributes, ['variation_parent_product']);
    }
}
