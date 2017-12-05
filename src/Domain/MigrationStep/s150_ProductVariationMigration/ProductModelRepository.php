<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\CreateProductModelCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductModelValuesBuilder;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;

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

    public function persist(ProductModel $productModel, DestinationPim $pim): ProductModel
    {
        $productModelData = [
            'family_variant' => $productModel->getFamilyVariantCode(),
            'categories' => $productModel->getCategories(),
            'parent' => $productModel->getParentIdentifier(),
            'values' => $productModel->getAttributeValues(),
        ];

        $this->console->execute(new CreateProductModelCommand($productModel->getIdentifier(), $productModelData), $pim);

        if (null === $productModel->getId()) {
            $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
                'SELECT id FROM pim_catalog_product_model WHERE code = "%s"',
                $productModel->getIdentifier()
            )), $pim)->getOutput();

            if(!isset($sqlResult[0]['id'])) {
                throw new ProductVariationMigrationException(sprintf(
                    'Unable to retrieve the product model %s. It seems that its creation failed.',
                    $productModel->getIdentifier()
                ));
            }

            $productModel = new ProductModel(
                (int) $sqlResult[0]['id'],
                $productModel->getIdentifier(),
                $productModel->getFamilyVariantCode(),
                $productModel->getCategories(),
                $productModel->getAttributeValues()
            );
        }

        return $productModel;
    }

    public function updateRawValuesAndCreatedForProduct(ProductModel $productModel, Pim $pim): void
    {
        $command = new MySqlExecuteCommand(
            'UPDATE pim_catalog_product_model AS product_model'
            .' INNER JOIN pim_catalog_product AS product ON product.identifier = product_model.code'
            .' SET product_model.raw_values = product.raw_values, product_model.created = product.created'
            .' WHERE product_model.id = '.$productModel->getId()
        );

        $this->console->execute($command, $pim);
    }
}
