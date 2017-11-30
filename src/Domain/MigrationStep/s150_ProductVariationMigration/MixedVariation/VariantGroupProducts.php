<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;

/**
 * Filters a list of products by variant group.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupProducts extends \FilterIterator
{
    /** @var string */
    private $variantGroupCode;

    public function __construct(array $products, string $variantGroupCode)
    {
        $this->variantGroupCode = $variantGroupCode;

        parent::__construct(new \ArrayIterator($products));
    }

    public function accept()
    {
        $product = $this->current();

        if ($product instanceof Product && $product->getVariantGroupCode() === $this->variantGroupCode) {
            return true;
        }

        return false;
    }
}
