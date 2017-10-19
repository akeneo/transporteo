<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Migrates variations from a combination of family and variant groups axes.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombinationMigrator
{
    /** @var ChainedConsole */
    private $console;

    /** @var VariantGroupRetriever */
    private $variantGroupRetriever;

    /** @var FamilyVariantImporter */
    private $familyVariantImporter;

    /** @var ProductModelImporter */
    private $productModelImporter;

    public function __construct(
        ChainedConsole $console,
        VariantGroupRetriever $variantGroupRetriever,
        FamilyVariantImporter $familyVariantImporter,
        ProductModelImporter $productModelImporter
    ) {
        $this->console = $console;
        $this->variantGroupRetriever = $variantGroupRetriever;
        $this->familyVariantImporter = $familyVariantImporter;
        $this->productModelImporter = $productModelImporter;
    }

    public function migrate(VariantGroupCombination $variantGroupCombination, Pim $pim)
    {
        $familyVariant = $this->createFamilyVariant($variantGroupCombination, $pim);

        $this->createProductModels($variantGroupCombination, $pim);
        $this->updateProductVariants($familyVariant, $variantGroupCombination, $pim);
    }

    /**
     * Creates a family variant corresponding to a variant group combination.
     */
    private function createFamilyVariant(VariantGroupCombination $variantGroupCombination, Pim $pim): FamilyVariant
    {
        $familyCode = $variantGroupCombination->getFamilyCode();
        $familyVariantCode = $variantGroupCombination->getFamilyVariantCode();
        $familyData = $this->variantGroupRetriever->retrieveFamilyData($familyCode, $pim);

        $variantAxes = $variantGroupCombination->getAxes();
        $variantGroupAttributes = $this->variantGroupRetriever->retrieveGroupAttributes($variantGroupCombination->getGroups()[0], $pim);
        $variantAttributes = array_diff($familyData['attributes'], $variantGroupAttributes, $variantAxes);

        $familyVariant = [
            'code' => $familyVariantCode,
            'family' => $familyCode,
            'variant-axes_1' => implode(',', $variantAxes),
            'variant-axes_2' => '',
            'variant-attributes_1' => implode(',', $variantAttributes),
            'variant-attributes_2' => '',
        ];

        $familyVariantLabels = $this->buildFamilyVariantLabels($familyData, $variantGroupCombination, $pim);

        foreach ($familyVariantLabels as $locale => $label) {
            $familyVariant['label-'.$locale] = $label;
        }

        $this->familyVariantImporter->import([$familyVariant], $pim);

        $familyVariantId = $this->variantGroupRetriever->retrieveFamilyVariantId($familyVariantCode, $pim);

        if (null === $familyVariantId) {
            throw new ProductVariationMigrationException(sprintf('Unable to retrieve the family variant %s. It seems that its creation failed.', $familyVariantCode));
        }

        return new FamilyVariant($familyVariantId, $familyVariantCode, $variantAttributes, $variantGroupAttributes);
    }

    /**
     * Build the labels of a family variant by adding the axes labels to the family labels.
     */
    private function buildFamilyVariantLabels(array $familyData, VariantGroupCombination $variantGroupCombination, Pim $pim): array
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

        return $familyVariantLabels;
    }

    /**
     * Creates the product models for a variant group combination.
     */
    private function createProductModels(VariantGroupCombination $variantGroupCombination, Pim $pim): void
    {
        $productModels = [];

        foreach ($variantGroupCombination->getGroups() as $variantGroupCode) {
            $categories = $this->variantGroupRetriever->retrieveVariantGroupCategories($variantGroupCode, $pim);

            $productModel = [
                'code' => $variantGroupCode,
                'family_variant' => $variantGroupCombination->getFamilyVariantCode(),
                'categories' => implode(',', $categories),
                'parent' => '',
            ];

            $producModelValues = $this->buildProductModelValues($variantGroupCode, $pim);
            $productModels[] = array_merge($productModel, $producModelValues);
        }

        $this->productModelImporter->import($productModels, $pim);
    }

    /**
     * Builds the attributes values of a product model from a variant group.
     */
    private function buildProductModelValues(string $variantGroupCode, Pim $pim): array
    {
        $producModelValues = [];
        $variantGroupValues = $this->variantGroupRetriever->retrieveGroupAttributeValues($variantGroupCode, $pim);

        foreach ($variantGroupValues as $attribute => $values) {
            foreach ($values as $value) {
                $attributeValueKey = $attribute;

                if (null !== $value['locale']) {
                    $attributeValueKey .= '-'.$value['locale'];
                }
                if (null !== $value['scope']) {
                    $attributeValueKey .= '-'.$value['scope'];
                }

                $producModelValues[$attributeValueKey] = $value['data'];
            }
        }
        
        return $producModelValues;
    }

    /**
     * Updates the product variants of the variant group :
     *  - Set the product model and the family variant
     *  - Remove the attributes that belong to the family variant.
     */
    private function updateProductVariants(FamilyVariant $familyVariant, VariantGroupCombination $variantGroupCombination, Pim $pim): void
    {
        foreach ($variantGroupCombination->getGroups() as $variantGroup) {
            $productModelId = $this->variantGroupRetriever->retrieveProductModelId($variantGroup, $pim);

            if (null === $productModelId) {
                throw new ProductVariationMigrationException(sprintf('Unable to retrieve the product model %s. It seems that its creation failed.', $variantGroup));
            }

            $query = sprintf(
                "UPDATE pim_catalog_product p"
                ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
                ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
                ." SET p.product_model_id = %s, p.family_variant_id = %s, p.product_type = 'variant_product'",
                $productModelId,
                $familyVariant->getId()
            );

            if (!empty($familyVariant->getProductModelAttributes())) {
                $query .= ", raw_values = JSON_REMOVE(raw_values";
                foreach ($familyVariant->getProductModelAttributes() as $attribute) {
                    $query .= sprintf(", '$.%s'", $attribute);
                }
                $query .= ")";
            }

            $query .= sprintf(" WHERE g.code = '%s'", $variantGroup);

            $this->console->execute(new MySqlExecuteCommand($query), $pim);
        }
    }
}
