<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Migrates products data related to an InnerVariationType.
 *  - Creates a product model for each product having variations from the IVB
 *  - Transforms each product variation into a product variant.
 *  - Remove the products migrated as product models.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationProductMigrator
{
    /** @var ChainedConsole */
    private $console;

    /** @var InnerVariationRetriever */
    private $innerVariationRetriever;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProductModelImporter */
    private $productModelImporter;

    public function __construct(
        ChainedConsole $console,
        InnerVariationRetriever $innerVariationRetriever,
        ProductModelImporter $productModelImporter,
        LoggerInterface $logger
    ) {
        $this->console = $console;
        $this->innerVariationRetriever = $innerVariationRetriever;
        $this->productModelImporter = $productModelImporter;
        $this->logger = $logger;
    }

    /**
     * Migrates the products data per family.
     */
    public function migrate(InnerVariationType $innerVariationType, Pim $pim): void
    {
        $innerVariationFamily = $this->innerVariationRetriever->retrieveInnerVariationFamily($innerVariationType, $pim);
        $parentFamilies = $this->innerVariationRetriever->retrieveParentFamiliesHavingProductsWithVariants($innerVariationType, $pim);

        foreach ($parentFamilies as $parentFamily) {
            $familyVariant = $this->innerVariationRetriever->retrieveFamilyVariant($parentFamily, $innerVariationFamily, $pim);

            if (empty($familyVariant)) {
                $this->logger->warning(sprintf(
                    'Unable to create the products models and the products variants of the InnerVariationType %s because the family variant has not been found.',
                    $innerVariationFamily->getCode()
                ));
            } else {
                $this->migrateFamilyVariantProducts($familyVariant, $parentFamily, $innerVariationFamily, $pim);
            }
        }
    }

    /**
     * Migrates the products (models and variants) for a given family variant.
     * The products models have to be created via import because of the tree data.
     * And then they're updated with Mysql for the attributes values and the creation date.
     */
    private function migrateFamilyVariantProducts(array $familyVariant, Family $parentFamily, Family $innerVariationFamily, Pim $pim): void
    {
        $productsModels = $this->createProductModels($parentFamily, $innerVariationFamily, $familyVariant, $pim);

        foreach ($productsModels as $productModel) {
            $productModelId = $this->innerVariationRetriever->retrieveProductModelId($productModel['identifier'], $pim);

            if (null === $productModelId) {
                $this->logger->warning(sprintf(
                    "The product model %s couldn't have been created. It and its variants will stay unchanged and they will have to be transformed manually.",
                    $productModel['identifier']
                ));
            } else {
                $productModel['id'] = $productModelId;

                $this->updateProductModel($productModel, $pim);
                $this->updateProductsVariants($productModel, $familyVariant, $parentFamily, $innerVariationFamily, $pim);
                $this->deleteProduct($productModel['identifier'], $pim);
            }
        }
    }

    /**
     * Creates the product models for a given family and family variant.
     */
    private function createProductModels(Family $parentFamily, Family $innerVariationFamily, array $familyVariant, Pim $pim): array
    {
        // The only way to retrieve the products models is to find those having at least one product child.
        $productsToBuild = $this->innerVariationRetriever->retrievesFamilyProductsHavingVariants($parentFamily->getId(), $innerVariationFamily->getId(), $pim);

        $productsModels = [];
        foreach ($productsToBuild as $product) {
            $categories = $this->innerVariationRetriever->retrieveProductCategories((int) $product['id'], $pim);

            $productsModels[] = [
                'code' => $product['identifier'],
                'family_variant' => $familyVariant['code'],
                'categories' => implode(',', $categories),
                'parent' => '',
            ];
        }

        try {
            $this->productModelImporter->import($productsModels, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to create the products models for the family variant %s : %s', $familyVariant['code'], $exception->getMessage()
            ));
            $productsToBuild = [];
        }

        return $productsToBuild;
    }

    /**
     * Updates the missing data of the products models that have not been migrated from the import.
     */
    public function updateProductModel(array $productModel, Pim $pim): void
    {
        $command = new MySqlExecuteCommand(
            'UPDATE pim_catalog_product_model AS product_model'
            .' INNER JOIN pim_catalog_product AS product ON product.identifier = product_model.code'
            .' SET product_model.raw_values = product.raw_values, product_model.created = product.created'
            .' WHERE product_model.id = '.$productModel['id']
        );

        try {
            $this->console->execute($command, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to update the attribute values and the creation date of the product model %s : %s',
                $productModel['identifier'],
                $exception->getMessage()
            ));
        }
    }

    /**
     * Update products to transform them into variants.
     */
    private function updateProductsVariants(array $productModel, array $familyVariant, Family $parentFamily, Family $innerVariationFamily, Pim $pim): void
    {
        $command = new MySqlExecuteCommand(sprintf(
            'UPDATE pim_catalog_product SET'
            .' family_id = %s, product_model_id = %s, family_variant_id = %s, created="%s",'
            .' product_type = "variant_product", raw_values = JSON_REMOVE(raw_values, \'$.variation_parent_product\')'
            .' WHERE family_id = %s'
            .' AND JSON_EXTRACT(raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = "%s"',
            $parentFamily->getId(),
            $productModel['id'],
            $familyVariant['id'],
            $productModel['created'],
            $innerVariationFamily->getId(),
            $productModel['identifier']
        ));

        try {
            $this->console->execute($command, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to transform the products variants of the product model %s : %s',
                $productModel['identifier'],
                $exception->getMessage()
            ));
        }
    }

    /**
     * Deletes a product that have been transformed to a product model.
     */
    private function deleteProduct(string $productCode, Pim $pim): void
    {
        $command = new DeleteProductCommand($productCode);

        try {
            $this->console->execute($command, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to delete the product %s : %s', $productCode, $exception->getMessage()
            ));
        }
    }
}
