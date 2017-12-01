<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\FamilyVariantBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\FamilyVariantLabelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariation;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantCodeBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantBuilderSpec extends ObjectBehavior
{
    public function let(
        FamilyVariantLabelBuilder $familyLabelBuilder,
        FamilyVariantCodeBuilder $familyVariantCodeBuilder,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        VariantGroupRepository $variantGroupRepository
    )
    {
        $this->beConstructedWith($familyLabelBuilder, $familyVariantCodeBuilder, $innerVariationFamilyMigrator, $variantGroupRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantBuilder::class);
    }

    public function it_builds_a_family_variant(
        $innerVariationFamilyMigrator,
        $variantGroupRepository,
        $familyVariantCodeBuilder,
        $familyLabelBuilder,
        DestinationPim $destinationPim
    )
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
        $mixedVariation = new MixedVariation($variantGroupCombination, $innerVariationType, new \ArrayObject());

        $innerVariationFamilyMigrator->migrateFamilyAttributes($family, $innerVariationFamily, $destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveGroupAttributes('group_1', $destinationPim)->willReturn(['group_att_1', 'group_att_2']);
        $familyVariantCodeBuilder->buildFromVariantGroupCombination($variantGroupCombination)->willReturn('family_variant');
        $familyLabelBuilder->build($variantGroupCombination, $innerVariationType, $destinationPim)->willReturn([
            'en_US' => 'Parent family VG axis 1 VG axis 2',
            'fr_FR' => 'Famille parent axis 2 VG IVT FR'
        ]);

        $familyVariant = new FamilyVariant(
            null,
            'family_variant',
            'family_parent',
            ['vg_axis_1', 'vg_axis_2'],
            ['ivt_axis'],
            ['family_parent_att_1'],
            ['variation_family_att_1'],
            [
                'en_US' => 'Parent family VG axis 1 VG axis 2',
                'fr_FR' => 'Famille parent axis 2 VG IVT FR'
            ]
        );

        $this->build($mixedVariation, $destinationPim)->shouldBeLike($familyVariant);
    }
}
