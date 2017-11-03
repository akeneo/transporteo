<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductModelValuesBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMigratorSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console, VariantGroupRetriever $variantGroupRetriever, ProductModelImporter $productModelImporter, ProductModelValuesBuilder $productModelValuesBuilder)
    {
        $this->beConstructedWith($console, $variantGroupRetriever, $productModelImporter, $productModelValuesBuilder);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductMigrator::class);
    }

    public function it_migrates_product_models(DestinationPim $pim, $variantGroupRetriever, $productModelImporter, $productModelValuesBuilder)
    {
        $variantGroupRetriever->retrieveVariantGroupCategories('vg_1', $pim)->willReturn(['vg_1_cat_1', 'vg_1_cat_2']);
        $productModelValuesBuilder->buildFromVariantGroup('vg_1', $pim)->willReturn([
            'vg_att_1' => 'VG 1 Att 1 value',
            'vg_att_2-en_US' => 'VG 1 Att 2 value US'
        ]);

        $productModelImporter->import([
            [
                'code' => 'vg_1',
                'family_variant' => 'family_variant_1',
                'categories' => 'vg_1_cat_1,vg_1_cat_2',
                'parent' => '',
                'vg_att_1' => 'VG 1 Att 1 value',
                'vg_att_2-en_US' => 'VG 1 Att 2 value US',
            ]
        ], $pim)->shouldBeCalled();

        $variantGroupRetriever->retrieveVariantGroupCategories('vg_2', $pim)->willReturn(['vg_2_cat_1']);
        $productModelValuesBuilder->buildFromVariantGroup('vg_2', $pim)->willReturn([
            'vg_att_1' => 'VG 2 Att 1 value',
            'vg_att_2-en_US' => 'VG 2 Att 2 value US',
            'vg_att_2-fr_FR' => null,
        ]);

        $productModelImporter->import([
            [
                'code' => 'vg_2',
                'family_variant' => 'family_variant_1',
                'categories' => 'vg_2_cat_1',
                'parent' => '',
                'vg_att_1' => 'VG 2 Att 1 value',
                'vg_att_2-en_US' => 'VG 2 Att 2 value US',
                'vg_att_2-fr_FR' => null,
            ]
        ], $pim)->shouldBeCalled();

        $variantGroupCombination = new VariantGroupCombination('family_1', 'family_variant_1', ['att_axe_1', 'att_axe_2'], ['vg_1', 'vg_2']);

        $this->migrateProductModels($variantGroupCombination, $pim);
    }

    public function it_migrates_product_variants(
        VariantGroupCombination $variantGroupCombination,
        FamilyVariant $familyVariant,
        DestinationPim $pim,
        $variantGroupRetriever,
        $console
    ) {
        $variantGroupCombination->getGroups()->willReturn(['vg_1', 'vg_2']);

        $familyVariant->getId()->willReturn(11);
        $familyVariant->getProductModelAttributes()->willReturn(['vg_att_1', 'vg_att_2', 'vg_att_3']);

        $variantGroupRetriever->retrieveProductModelId('vg_1' ,$pim)->willReturn(41);
        $variantGroupRetriever->retrieveProductModelId('vg_2' ,$pim)->willReturn(42);

        $console->execute(new MySqlExecuteCommand(
            "UPDATE pim_catalog_product p"
            ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
            ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
            ." SET p.product_model_id = 41, p.family_variant_id = 11, p.product_type = 'variant_product'"
            .", raw_values = JSON_REMOVE(raw_values, '$.vg_att_1', '$.vg_att_2', '$.vg_att_3')"
            ." WHERE g.code = 'vg_1'"
        ), $pim)->shouldBeCalled();

        $console->execute(new MySqlExecuteCommand(
            "UPDATE pim_catalog_product p"
            ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
            ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
            ." SET p.product_model_id = 42, p.family_variant_id = 11, p.product_type = 'variant_product'"
            .", raw_values = JSON_REMOVE(raw_values, '$.vg_att_1', '$.vg_att_2', '$.vg_att_3')"
            ." WHERE g.code = 'vg_2'"
        ), $pim)->shouldBeCalled();

        $this->migrateProductVariants($familyVariant, $variantGroupCombination, $pim);
    }
}
