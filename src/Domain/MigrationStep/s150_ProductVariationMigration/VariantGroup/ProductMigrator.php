<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Migrates products of variant groups.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMigrator
{
    /** @var ChainedConsole */
    private $console;

    /** @var VariantGroupRetriever */
    private $variantGroupRetriever;

    /** @var ProductModelImporter */
    private $productModelImporter;

    /** @var ProductModelValuesBuilder */
    private $productModelValuesBuilder;

    public function __construct(
        ChainedConsole $console,
        VariantGroupRetriever $variantGroupRetriever,
        ProductModelImporter $productModelImporter,
        ProductModelValuesBuilder $productModelValuesBuilder
    ) {
        $this->console = $console;
        $this->variantGroupRetriever = $variantGroupRetriever;
        $this->productModelImporter = $productModelImporter;
        $this->productModelValuesBuilder = $productModelValuesBuilder;
    }

    /**
     * Creates the product models for a variant group combination.
     */
    public function migrateProductModels(VariantGroupCombination $variantGroupCombination, DestinationPim $pim): void
    {
        foreach ($variantGroupCombination->getGroups() as $variantGroupCode) {
            $categories = $this->variantGroupRetriever->retrieveVariantGroupCategories($variantGroupCode, $pim);

            $productModel = [
                'code' => $variantGroupCode,
                'family_variant' => $variantGroupCombination->getFamilyVariantCode(),
                'categories' => implode(',', $categories),
                'parent' => '',
            ];

            $producModelValues = $this->productModelValuesBuilder->buildFromVariantGroup($variantGroupCode, $pim);
            $productModel = array_merge($productModel, $producModelValues);

            // One import per product because the variant groups can have different attribute values.
            // It will be improved when it will be possible to create a product model by the API.
            $this->productModelImporter->import([$productModel], $pim);
        }
    }

    /**
     * Updates the product variants of the variant group :
     *  - Set the product model and the family variant
     *  - Remove the attributes that belong to the family variant.
     */
    public function migrateProductVariants(FamilyVariant $familyVariant, VariantGroupCombination $variantGroupCombination, DestinationPim $pim): void
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
