<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantLabelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyCreator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyCreatorSpec extends ObjectBehavior
{
    public function let(FamilyRepository $familyRepository, VariantGroupRepository $variantGroupRepository, FamilyVariantLabelBuilder $familyVariantLabelBuilder)
    {
        $this->beConstructedWith($familyRepository, $variantGroupRepository, $familyVariantLabelBuilder);
    }

    public function i_is_initializable()
    {
        $this->shouldHaveType(FamilyCreator::class);
    }

    public function it_successfully_creates_a_family_variant(DestinationPim $destinationPim, $variantGroupRepository, $familyRepository, $familyVariantLabelBuilder)
    {
        $family = new Family(11, 'family_1', [
            'attributes' => [
                'att_1', 'att_2', 'vg_att_1', 'vg_att_2', 'vg_att_3', 'att_axe_1', 'att_axe_2'
            ],
            'labels' => [
                'en_US' => 'Family 1 US',
                'fr_FR' => 'Family 1 FR',
            ]
        ]);
        $variantGroupCombination = new VariantGroupCombination(
            $family,
            'family_variant_1',
            ['att_axe_1', 'att_axe_2'],
            ['vg_1', 'vg_2'],
            ['vg_att_1', 'vg_att_2', 'vg_att_3']
        );

        $familyVariantLabelBuilder->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->willReturn([
            'en_US' => 'Family 1 US Axe 1 US Axe 2 US',
            'fr_FR' => 'Family 1 FR Axe 1 FR Axe 2 FR',
        ]);

        $familyRepository->persistFamilyVariant(new FamilyVariant(
            null,
            'family_variant_1',
            'family_1',
            ['att_axe_1', 'att_axe_2'],
            [],
            ['att_1', 'att_2'],
            [],
            [
                'en_US' => 'Family 1 US Axe 1 US Axe 2 US',
                'fr_FR' => 'Family 1 FR Axe 1 FR Axe 2 FR',
            ]
        ), $destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveFamilyVariantId('family_variant_1', $destinationPim)->willReturn(11);

        $this->createFamilyVariant($variantGroupCombination, $destinationPim);
    }

    public function it_throws_an_exception_if_it_fails_to_create_a_family_variant(
        DestinationPim $destinationPim, $variantGroupRepository, $familyRepository, $familyVariantLabelBuilder
    ) {
        $family = new Family(11, 'family_1', [
            'attributes' => [
                'att_1', 'att_2', 'vg_att_1', 'vg_att_2', 'vg_att_3', 'att_axe_1', 'att_axe_2'
            ],
            'labels' => [
                'en_US' => 'Family 1 US',
                'fr_FR' => 'Family 1 FR',
            ]
        ]);
        $variantGroupCombination = new VariantGroupCombination(
            $family,
            'family_variant_1',
            ['att_axe_1', 'att_axe_2'],
            ['vg_1', 'vg_2'],
            ['vg_att_1', 'vg_att_2', 'vg_att_3']
        );

        $familyVariantLabelBuilder->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->willReturn([
            'en_US' => 'Family 1 US Axe 1 US Axe 2 US',
            'fr_FR' => 'Family 1 FR Axe 1 FR Axe 2 FR',
        ]);

        $familyRepository->persistFamilyVariant(new FamilyVariant(
            null,
            'family_variant_1',
            'family_1',
            ['att_axe_1', 'att_axe_2'],
            [],
            ['att_1', 'att_2'],
            [],
            [
                'en_US' => 'Family 1 US Axe 1 US Axe 2 US',
                'fr_FR' => 'Family 1 FR Axe 1 FR Axe 2 FR',
            ]
        ), $destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveFamilyVariantId('family_variant_1', $destinationPim)->willReturn(null);

        $this->shouldThrow(new ProductVariationMigrationException('Unable to retrieve the family variant family_variant_1. It seems that its creation failed.'))
            ->during('createFamilyVariant', [$variantGroupCombination, $destinationPim]);
    }
}
