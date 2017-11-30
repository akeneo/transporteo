<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;

/**
 * Data needed to perform a migration of a mixed products variation (with IVB and variant-group both)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariation
{
    /** @var VariantGroupCombination */
    private $variantGroupCombination;

    /** @var InnerVariationType */
    private $innerVariationType;

    /** @var array */
    private $productsHavingVariants;

    /** @var \Traversable */
    private $variantGroups;

    public function __construct(
        VariantGroupCombination $variantGroupCombination,
        InnerVariationType $innerVariationType,
        array $productsHavingVariants,
        \Traversable $variantGroups
    ){
        $this->variantGroupCombination = $variantGroupCombination;
        $this->innerVariationType = $innerVariationType;
        $this->productsHavingVariants = $productsHavingVariants;
        $this->variantGroups = $variantGroups;
    }

    public function getVariantGroupCombination(): VariantGroupCombination
    {
        return $this->variantGroupCombination;
    }

    public function getInnerVariationType(): InnerVariationType
    {
        return $this->innerVariationType;
    }

    public function getProductsHavingVariants(): array
    {
        return $this->productsHavingVariants;
    }

    public function getVariantGroupProductsHavingVariants(string $variantGroupCode): \Traversable
    {
       return new VariantGroupProducts($this->productsHavingVariants, $variantGroupCode);
    }

    public function getVariantGroups(): \Traversable
    {
        return $this->variantGroups;
    }
}
