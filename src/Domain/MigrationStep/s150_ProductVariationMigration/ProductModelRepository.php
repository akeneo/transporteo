<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductModelValuesBuilder;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Repository for product models data on the destination PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var ProductModelImporter */
    private $productModelImporter;

    /** @var ProductModelValuesBuilder */
    private $productModelValuesBuilder;

    public function __construct(ChainedConsole $console, ProductModelImporter $productModelImporter, ProductModelValuesBuilder $productModelValuesBuilder)
    {
        $this->console = $console;
        $this->productModelImporter = $productModelImporter;
        $this->productModelValuesBuilder = $productModelValuesBuilder;
    }

    // TODO: persist via the API and remove the dependency to ProductModelValuesBuilder.
    public function persist(ProductModel $productModel, DestinationPim $pim): ProductModel
    {
        $productModelData = [
            'code' => $productModel->getIdentifier(),
            'family_variant' => $productModel->getFamilyVariantCode(),
            'categories' => implode(',', $productModel->getCategories()),
            'parent' => '',
        ];

        $productModelValues = $this->productModelValuesBuilder->build($productModel);
        $productModelData = array_merge($productModelData, $productModelValues);

        $this->productModelImporter->import([$productModelData], $pim);

        if (null === $productModel->getId()) {
            $id = $this->retrieveProductModelId($this->identifier, $pim);

            $productModel = new ProductModel(
                $id,
                $productModel->getIdentifier(),
                $productModel->getFamilyVariantCode(),
                $productModel->getCategories(),
                $productModel->getAttributeValues()
            );
        }

        return $productModel;
    }

    private function retrieveProductModelId(string $productModelCode, DestinationPim $pim): int
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_product_model WHERE code = "%s"',
            $productModelCode
        )), $pim)->getOutput();

        if(!isset($sqlResult[0]['id'])) {
            throw new ProductVariationMigrationException(sprintf(
                'Unable to retrieve the product model %s. It seems that its creation failed.',
                $productModelCode
            ));
        }

        return (int) $sqlResult[0]['id'];
    }
}
