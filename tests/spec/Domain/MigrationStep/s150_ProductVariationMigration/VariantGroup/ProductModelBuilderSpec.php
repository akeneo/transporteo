<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductModelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelBuilderSpec extends ObjectBehavior
{
    public function let(VariantGroupRepository $variantGroupRepository)
    {
        $this->beConstructedWith($variantGroupRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelBuilder::class);
    }

    public function it_builds_a_product_model_from_a_variant_group(
        $variantGroupRepository,
        FamilyVariant $familyVariant,
        DestinationPim $pim
    ) {
        $attributeValues = [
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
                ]
            ]
        ];

        $variantGroupRepository->retrieveVariantGroupCategories('vg_1', $pim)->willReturn(['vg_1_cat_1', 'vg_1_cat_2']);
        $variantGroupRepository->retrieveGroupAttributeValues('vg_1', $pim)->willReturn($attributeValues);

        $familyVariant->getCode()->willReturn('family_variant_1');

        $expectedProductModel = new ProductModel(
            null,
            'vg_1',
            'family_variant_1',
            ['vg_1_cat_1','vg_1_cat_2'],
            $attributeValues
        );

        $this->buildFromVariantGroup('vg_1', $familyVariant, $pim)->shouldBeLike($expectedProductModel);
    }
}
