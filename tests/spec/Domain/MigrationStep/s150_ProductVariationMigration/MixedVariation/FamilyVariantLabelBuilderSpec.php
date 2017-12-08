<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\FamilyVariantLabelBuilder;
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
    public function let(VariantGroupRepository $variantGroupRepository, InnerVariationTypeRepository $innerVariationTypeRepository)
    {
        $this->beConstructedWith($variantGroupRepository, $innerVariationTypeRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantLabelBuilder::class);
    }

    public function it_builds_the_labels_for_a_family_variant(DestinationPim $pim, $variantGroupRepository, $innerVariationTypeRepository)
    {
        $family = new Family(41, 'family_parent', [
            'attributes' => ['family_parent_att_1', 'vg_axis_1', 'vg_axis_2', 'variation_family_att_1', 'group_att_1', 'group_att_2'],
            'labels' => [
                'en_US' => 'Parent family',
                'fr_FR' => 'Famille parent'
            ]
        ]);

        $innerVariationFamily = new Family(42, 'variation_family', [
            'attributes' => ['variation_family_att_1', 'ivt_axis', 'variation_parent_product'],
            'labels' => [
                'en_US' => 'Variation family',
                'fr_FR' => 'Famille de variation'
            ]
        ]);

        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $innerVariationFamily, [['code' => 'ivt_axis']]);

        $variantGroupRepository->retrieveAttributeData('vg_axis_1', $pim)->willReturn([
            'labels' => [
                'en_US' => 'VG axis 1',
                'fr_FR' => null,
            ]
        ]);

        $variantGroupRepository->retrieveAttributeData('vg_axis_2', $pim)->willReturn([
            'labels' => [
                'en_US' => 'VG axis 2',
                'fr_FR' => 'axis 2 VG',
            ]
        ]);

        $innerVariationTypeRepository->getLabel($innerVariationType, 'en_US', $pim)->willReturn('');
        $innerVariationTypeRepository->getLabel($innerVariationType, 'fr_FR', $pim)->willReturn('IVT FR');

        $this->build($variantGroupCombination, $innerVariationType, $pim)->shouldReturn([
            'en_US' => 'Parent family VG axis 1 VG axis 2',
            'fr_FR' => 'Famille parent axis 2 VG IVT FR'
        ]);
    }
}
