<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantLabelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantLabelBuilderSpec extends ObjectBehavior
{
    public function let(VariantGroupRepository $variantGroupRepository)
    {
        $this->beConstructedWith($variantGroupRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantLabelBuilder::class);
    }

    public function it_builds_labels_from_a_variant_group_combination($variantGroupRepository, VariantGroupCombination $variantGroupCombination, DestinationPim $pim)
    {
        $family = new Family(1, 'family_1', [
            'code' => 'family_1',
            'attributes' => [
                'att_1', 'att_2', 'vg_att_1', 'vg_att_2', 'vg_att_3', 'att_axis_1', 'att_axis_2'
            ],
            'labels' => [
                'en_US' => 'Family 1 US',
                'fr_FR' => 'Family 1 FR',
            ]
        ]);

        $variantGroupCombination->getFamily()->willReturn($family);
        $variantGroupCombination->getAxes()->willReturn(['att_axis_1', 'att_axis_2']);

        $variantGroupRepository->retrieveAttributeData('att_axis_1', $pim)->willReturn([
            'code' => 'att_axis_1',
            'labels' => [
                'en_US' => 'axis 1 US',
                'fr_FR' => 'axis 1 FR',
            ]
        ]);
        $variantGroupRepository->retrieveAttributeData('att_axis_2', $pim)->willReturn([
            'code' => 'att_axis_2',
            'labels' => [
                'en_US' => 'axis 2 US',
                'fr_FR' => null,
            ]
        ]);

        $this->buildFromVariantGroupCombination($variantGroupCombination, $pim)->shouldReturn([
            'en_US' => 'Family 1 US axis 1 US axis 2 US',
            'fr_FR' => 'Family 1 FR axis 1 FR',
        ]);
    }
}
