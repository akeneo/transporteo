<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModel;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Builds a product model from a variant group.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelBuilder
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    public function __construct(VariantGroupRepository $variantGroupRepository)
    {
        $this->variantGroupRepository = $variantGroupRepository;
    }

    public function buildFromVariantGroup(string $variantGroupCode, FamilyVariant $familyVariant, DestinationPim $pim)
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
}
