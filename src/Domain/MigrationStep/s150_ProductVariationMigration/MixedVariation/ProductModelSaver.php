<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\Command\Api\CreateProductModelCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * TODO: to replace by ProductModelRepository when it will persist via the API.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelSaver
{
    /** @var ChainedConsole */
    private $console;

    /** @var ProductModelRepository */
    private $productModelRepository;

    public function __construct(ChainedConsole $console, ProductModelRepository $productModelRepository)
    {
        $this->console = $console;
        $this->productModelRepository = $productModelRepository;
    }

    public function save(ProductModel $productModel, DestinationPim $pim): ProductModel
    {
        $productModelData = [
            'family_variant' => $productModel->getFamilyVariantCode(),
            'categories' => $productModel->getCategories(),
            'parent' => $productModel->getParentIdentifier(),
            'values' => $productModel->getAttributeValues(),
        ];

        $this->console->execute(new CreateProductModelCommand($productModel->getIdentifier(), $productModelData), $pim);

        $productModelId = $this->productModelRepository->getProductModelId($productModel->getIdentifier(), $pim);

        if (null === $productModelId) {
            throw new ProductVariationMigrationException(sprintf('Unable to retrieve the product model %s. It seems that its creation failed.', $parentProduct->getIdentifier()));
        }

        return new ProductModel(
            $productModelId,
            $productModel->getIdentifier(),
            $productModel->getFamilyVariantCode(),
            $productModel->getCategories(),
            $productModel->getAttributeValues(),
            $productModel->getParentIdentifier()
        );
    }
}
