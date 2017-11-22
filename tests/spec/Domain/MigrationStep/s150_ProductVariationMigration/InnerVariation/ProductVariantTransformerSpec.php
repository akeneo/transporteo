<?php

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductVariantTransformerSpec extends ObjectBehavior
{
    function let(ChainedConsole $chainedConsole)
    {
        $this->beConstructedWith($chainedConsole);
    }

    function it_transform_a_product_model(
        $chainedConsole,
        ProductModel $productModel,
        FamilyVariant $familyVariant,
        Family $parentFamily,
        Family $innerVariationFamily,
        Pim $pim
    ) {
        $parentFamily->getId()->willReturn(1);
        $productModel->getId()->willReturn(1);
        $familyVariant->getId()->willReturn(1);
        $innerVariationFamily->getId()->willReturn(1);
        $productModel->getIdentifier()->willReturn(Argument::type('string'));

        $chainedConsole->execute(Argument::any(), $pim);

        $this->transform($productModel, $familyVariant, $parentFamily, $innerVariationFamily, $pim);
    }
}
