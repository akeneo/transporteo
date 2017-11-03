<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Repository for product models data.
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

    public function __construct(ChainedConsole $console, ProductModelImporter $productModelImporter)
    {
        $this->console = $console;
        $this->productModelImporter = $productModelImporter;
    }

    public function persist(ProductModel $productModel, DestinationPim $pim): void
    {
        $productModelData = [
            'code' => $productModel->getIdentifier(),
            'family_variant' => $productModel->getFamilyVariantCode(),
            'categories' => implode(',', $productModel->getCategories()),
            'parent' => '',
        ];

        $productModelData = array_merge($productModelData, $productModel->getValues());

        $this->productModelImporter->import([$productModelData], $pim);
    }

    public function retrieveProductModelId(string $productModelCode, DestinationPim $pim): ?int
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_product_model WHERE code = "%s"',
            $productModelCode
        )), $pim)->getOutput();

        return isset($sqlResult[0]['id']) ? (int) $sqlResult[0]['id'] : null;
    }
}
