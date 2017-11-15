<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductVariantTransformer;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVariantTransformerSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console)
    {
        $this->beConstructedWith($console);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductVariantTransformer::class);
    }

    public function it_transforms_products_into_product_variants($console, ProductModel $productModel, FamilyVariant $familyVariant, DestinationPim $pim)
    {
        $familyVariant->getId()->willReturn(11);
        $productModel->getId()->willReturn(41);
        $productModel->getIdentifier()->willReturn('vg_1');
        $productModel->getAttributeValues()->willReturn([
            'vg_att_1' => [],
            'vg_att_2' => [],
            'vg_att_3' => [],
        ]);

        $console->execute(new MySqlExecuteCommand(
            "UPDATE pim_catalog_product p"
            ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
            ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
            ." SET p.product_model_id = 41, p.family_variant_id = 11, p.product_type = 'variant_product'"
            .", raw_values = JSON_REMOVE(raw_values, '$.vg_att_1', '$.vg_att_2', '$.vg_att_3')"
            ." WHERE g.code = 'vg_1'"
        ), $pim)->shouldBeCalled();

        $this->transformFromProductModel($productModel, $familyVariant, $pim);
    }
}
