<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantLabelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantLabelBuilderSpec extends ObjectBehavior
{
    public function let(VariantGroupRetriever $variantGroupRetriever)
    {
        $this->beConstructedWith($variantGroupRetriever);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantLabelBuilder::class);
    }

    public function it_builds_labels_from_a_variant_group_combination($variantGroupRetriever, VariantGroupCombination $variantGroupCombination, Pim $pim)
    {
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

        $variantGroupCombination->getAxes()->willReturn(['att_axe_1', 'att_axe_2']);

        $variantGroupRetriever->retrieveAttributeData('att_axe_1', $pim)->willReturn([
            'code' => 'att_axe_1',
            'labels' => [
                'en_US' => 'Axe 1 US',
                'fr_FR' => 'Axe 1 FR',
            ]
        ]);
        $variantGroupRetriever->retrieveAttributeData('att_axe_2', $pim)->willReturn([
            'code' => 'att_axe_2',
            'labels' => [
                'en_US' => 'Axe 2 US',
                'fr_FR' => null,
            ]
        ]);

        $this->buildFromVariantGroupCombination($familyData, $variantGroupCombination, $pim)->shouldReturn([
            'en_US' => 'Family 1 US Axe 1 US Axe 2 US',
            'fr_FR' => 'Family 1 FR Axe 1 FR',
        ]);
    }
}
