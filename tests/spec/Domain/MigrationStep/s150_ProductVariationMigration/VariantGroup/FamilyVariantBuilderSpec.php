<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantCodeBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantLabelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantBuilderSpec extends ObjectBehavior
{
    public function let(FamilyVariantLabelBuilder $familyVariantLabelBuilder, FamilyVariantCodeBuilder $familyVariantCodeBuilder)
    {
        $this->beConstructedWith($familyVariantLabelBuilder, $familyVariantCodeBuilder);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantBuilder::class);
    }

    public function it_successfully_creates_a_family_variant(DestinationPim $destinationPim, $familyVariantLabelBuilder, $familyVariantCodeBuilder)
    {
        $family = new Family(11, 'family_1', [
            'attributes' => [
                'att_1', 'att_2', 'vg_att_1', 'vg_att_2', 'vg_att_3', 'att_axis_1', 'att_axis_2'
            ],
            'labels' => [
                'en_US' => 'Family 1 US',
                'fr_FR' => 'Family 1 FR',
            ]
        ]);
        $variantGroupCombination = new VariantGroupCombination(
            $family,
            ['att_axis_1', 'att_axis_2'],
            ['vg_1', 'vg_2'],
            ['vg_att_1', 'vg_att_2', 'vg_att_3']
        );

        $familyVariantLabelBuilder->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->willReturn([
            'en_US' => 'Family 1 US axis 1 US axis 2 US',
            'fr_FR' => 'Family 1 FR axis 1 FR axis 2 FR',
        ]);

        $familyVariantCodeBuilder->buildFromVariantGroupCombination($variantGroupCombination)->willReturn('family_1_att_axis_1_att_axis_2');

        $familyVariant = new FamilyVariant(
            null,
            'family_1_att_axis_1_att_axis_2',
            'family_1',
            ['att_axis_1', 'att_axis_2'],
            [],
            ['att_1', 'att_2'],
            [],
            [
                'en_US' => 'Family 1 US axis 1 US axis 2 US',
                'fr_FR' => 'Family 1 FR axis 1 FR axis 2 FR',
            ]
        );

        $this->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->shouldBeLike($familyVariant);
    }
}
