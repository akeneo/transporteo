<?php

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;

class ProductModelBuilderSpec extends ObjectBehavior
{
    function let(ProductRepository $productRepository)
    {
        $this->beConstructedWith($productRepository);
    }

    function it_build_a_product_model(
        $productRepository,
        Product $product,
        FamilyVariant $familyVariant,
        Pim $pim
    ) {
        $product->getId()->willReturn(1);
        $productRepository->getCategoryCodes(1, $pim)->willReturn([1, 2]);

        $product->getIdentifier()->willReturn('product_1');
        $familyVariant->getCode()->willReturn('family_variant');

        $this->build($product, $familyVariant, $pim);
    }
}
