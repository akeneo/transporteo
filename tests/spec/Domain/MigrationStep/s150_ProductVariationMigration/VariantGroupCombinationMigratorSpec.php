<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombinationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombinationMigratorSpec extends ObjectBehavior
{
    public function let(
        ChainedConsole $console,
        VariantGroupRetriever $variantGroupRetriever,
        FamilyVariantImporter $familyVariantImporter,
        ProductModelImporter $productModelImporter
    )
    {
        $this->beConstructedWith($console, $variantGroupRetriever, $familyVariantImporter, $productModelImporter);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VariantGroupCombinationMigrator::class);
    }

    public function it_migrates_successfully_a_variant_group_combination(
        DestinationPim $destinationPim,
        $console,
        $variantGroupRetriever,
        $familyVariantImporter,
        $productModelImporter
    )
    {
        $variantGroupCombination = new VariantGroupCombination('family_1', 'family_variant_1', ['att_axe_1', 'att_axe_2'], ['vg_1', 'vg_2']);

        $familyData = [
            'code' => 'family_1',
            'attributes' => [
                'att_1', 'att_2', 'vg_att_1', 'vg_att_2', 'vg_att_3', 'att_axe_1', 'att_axe_2'
            ],
            'labels' => [
                'en_US' => 'Family 1 US',
                'fr_FR' => 'Family 1 FR',
            ]
        ];

        $variantGroupRetriever->retrieveFamilyData('family_1', $destinationPim)->willReturn($familyData);
        $variantGroupRetriever->retrieveGroupAttributes('vg_1', $destinationPim)->willReturn(['vg_att_1', 'vg_att_2', 'vg_att_3']);

        $variantGroupRetriever->retrieveAttributeData('att_axe_1', $destinationPim)->willReturn([
            'code' => 'att_axe_1',
            'labels' => [
                'en_US' => 'Axe 1 US',
                'fr_FR' => 'Axe 1 FR',
            ]
        ]);
        $variantGroupRetriever->retrieveAttributeData('att_axe_2', $destinationPim)->willReturn([
            'code' => 'att_axe_2',
            'labels' => [
                'en_US' => 'Axe 2 US',
                'fr_FR' => 'Axe 2 FR',
            ]
        ]);

        $familyVariantImporter->import([[
            'code' => 'family_variant_1',
            'family' => 'family_1',
            'variant-axes_1' => 'att_axe_1,att_axe_2',
            'variant-axes_2' => '',
            'variant-attributes_1' => 'att_1,att_2',
            'variant-attributes_2' => '',
            'label-en_US' => 'Family 1 US Axe 1 US Axe 2 US',
            'label-fr_FR' => 'Family 1 FR Axe 1 FR Axe 2 FR',
        ]], $destinationPim)->shouldBeCalled();

        $variantGroupRetriever->retrieveFamilyVariantId('family_variant_1', $destinationPim)->willReturn(11);

        $variantGroupRetriever->retrieveVariantGroupCategories('vg_1', $destinationPim)->willReturn(['vg_1_cat_1', 'vg_1_cat_2']);
        $variantGroupRetriever->retrieveGroupAttributeValues('vg_1', $destinationPim)->willReturn([
            'vg_att_1' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'VG 1 Att 1 value'
                ]
            ],
            'vg_att_2' => [
                [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'VG 1 Att 2 value US'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope' => null,
                    'data' => 'VG 1 Att 2 value FR'
                ],
            ],
            'vg_att_3' => [
                [
                    'locale' => null,
                    'scope' => 'ecommerce',
                    'data' => [
                        [
                            'amount' => 99,
                            'currency' => 'USD'
                        ],
                        [
                            'amount' => 110,
                            'currency' => 'EUR'
                        ]
                    ]
                ]
            ]
        ]);

        $productModelImporter->import([
            [
                'code' => 'vg_1',
                'family_variant' => 'family_variant_1',
                'categories' => 'vg_1_cat_1,vg_1_cat_2',
                'parent' => '',
                'vg_att_1' => 'VG 1 Att 1 value',
                'vg_att_2-en_US' => 'VG 1 Att 2 value US',
                'vg_att_2-fr_FR' => 'VG 1 Att 2 value FR',
                'vg_att_3-ecommerce-USD' => 99,
                'vg_att_3-ecommerce-EUR' => 110
            ]
        ], $destinationPim)->shouldBeCalled();

        $variantGroupRetriever->retrieveVariantGroupCategories('vg_2', $destinationPim)->willReturn(['vg_2_cat_1']);
        $variantGroupRetriever->retrieveGroupAttributeValues('vg_2', $destinationPim)->willReturn([
            'vg_att_1' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'VG 2 Att 1 value'
                ]
            ],
            'vg_att_2' => [
                [
                    'locale' => 'en_US',
                    'scope' => null,
                    'data' => 'VG 2 Att 2 value US'
                ],
                [
                    'locale' => 'fr_FR',
                    'scope' => null,
                    'data' => null
                ],
            ],
            'vg_att_4' => [
                [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'data' => [
                        'amount' => 345,
                        'unit' => 'gram'
                    ]
                ],
                [
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce',
                    'data' => [
                        'amount' => 3,
                        'unit' => 'kilogram'
                    ]
                ]
            ]
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
                'vg_att_4-en_US-ecommerce' => 345,
                'vg_att_4-en_US-ecommerce-unit' => 'gram',
                'vg_att_4-fr_FR-ecommerce' => 3,
                'vg_att_4-fr_FR-ecommerce-unit' => 'kilogram'
            ]
        ], $destinationPim)->shouldBeCalled();

        $variantGroupRetriever->retrieveProductModelId('vg_1' ,$destinationPim)->willReturn(41);
        $variantGroupRetriever->retrieveProductModelId('vg_2' ,$destinationPim)->willReturn(42);

        $console->execute(new MySqlExecuteCommand(
            "UPDATE pim_catalog_product p"
            ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
            ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
            ." SET p.product_model_id = 41, p.family_variant_id = 11, p.product_type = 'variant_product'"
            .", raw_values = JSON_REMOVE(raw_values, '$.vg_att_1', '$.vg_att_2', '$.vg_att_3')"
            ." WHERE g.code = 'vg_1'"
        ), $destinationPim)->shouldBeCalled();

        $console->execute(new MySqlExecuteCommand(
            "UPDATE pim_catalog_product p"
            ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
            ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
            ." SET p.product_model_id = 42, p.family_variant_id = 11, p.product_type = 'variant_product'"
            .", raw_values = JSON_REMOVE(raw_values, '$.vg_att_1', '$.vg_att_2', '$.vg_att_3')"
            ." WHERE g.code = 'vg_2'"
        ), $destinationPim)->shouldBeCalled();

        $this->migrate($variantGroupCombination, $destinationPim);
    }
}
