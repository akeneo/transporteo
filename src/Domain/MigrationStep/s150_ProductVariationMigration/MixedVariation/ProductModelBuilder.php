<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Builds product models for a mixed variation (IVB + variant-group)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelBuilder
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var ProductRepository */
    private $productRepository;

    public function __construct(VariantGroupRepository $variantGroupRepository, ProductRepository $productRepository)
    {
        $this->variantGroupRepository = $variantGroupRepository;
        $this->productRepository = $productRepository;
    }

    public function buildRootProductModel(string $variantGroupCode, FamilyVariant $familyVariant, DestinationPim $pim): ProductModel
    {
        $categories = $this->variantGroupRepository->retrieveVariantGroupCategories($variantGroupCode, $pim);
        $productModelAttributeValues = $this->variantGroupRepository->retrieveGroupAttributeValues($variantGroupCode, $pim);

        return new ProductModel(
            null,
            $variantGroupCode,
            $familyVariant->getCode(),
            $categories,
            $productModelAttributeValues
        );
    }

    public function buildSubProductModel(ProductModel $parentProductModel, Product $parentProduct, FamilyVariant $familyVariant, DestinationPim $pim): ProductModel
    {
        $parentProductData = $this->productRepository->getStandardData($parentProduct->getIdentifier(), $pim);

        return new ProductModel(
            null,
            $parentProduct->getIdentifier(),
            $familyVariant->getCode(),
            $parentProductData['categories'],
            $parentProductData['values'],
            $parentProductModel->getIdentifier()
        );
    }
}
