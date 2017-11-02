<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyCreator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyCreatorSpec extends ObjectBehavior
{
    public function let(FamilyVariantImporter $familyVariantImporter, VariantGroupRetriever $variantGroupRetriever)
    {
        $this->beConstructedWith($familyVariantImporter, $variantGroupRetriever);
    }

    public function i_is_initializable()
    {
        $this->shouldHaveType(FamilyCreator::class);
    }

    public function it_successfully_creates_a_family_variant(DestinationPim $destinationPim, $variantGroupRetriever, $familyVariantImporter)
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

        $this->createFamilyVariant($variantGroupCombination, $destinationPim);
    }

    public function it_throws_an_exception_if_it_fails_to_create_a_family_variant(
        DestinationPim $destinationPim, $variantGroupRetriever, $familyVariantImporter
    ) {
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

        $variantGroupRetriever->retrieveFamilyVariantId('family_variant_1', $destinationPim)->willReturn(null);

        $this->shouldThrow(new ProductVariationMigrationException('Unable to retrieve the family variant family_variant_1. It seems that its creation failed.'))
            ->during('createFamilyVariant', [$variantGroupCombination, $destinationPim]);
    }
}
