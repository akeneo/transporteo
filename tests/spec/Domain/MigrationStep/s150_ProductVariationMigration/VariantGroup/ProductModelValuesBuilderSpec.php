<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductModelValuesBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelValuesBuilderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelValuesBuilder::class);
    }

    public function it_builds_values_from_a_variant_group(ProductModel $productModel)
    {
        $productModel->getAttributeValues()->willReturn([
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

        $this->build($productModel)->shouldReturn([
            'vg_att_1' => 'VG 1 Att 1 value',
            'vg_att_2-en_US' => 'VG 1 Att 2 value US',
            'vg_att_2-fr_FR' => 'VG 1 Att 2 value FR',
            'vg_att_3-ecommerce-USD' => 99,
            'vg_att_3-ecommerce-EUR' => 110,
            'vg_att_4-en_US-ecommerce' => 345,
            'vg_att_4-en_US-ecommerce-unit' => 'gram',
            'vg_att_4-fr_FR-ecommerce' => 3,
            'vg_att_4-fr_FR-ecommerce-unit' => 'kilogram'
        ]);
    }
}
