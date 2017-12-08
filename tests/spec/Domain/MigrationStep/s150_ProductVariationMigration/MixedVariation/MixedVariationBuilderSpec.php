<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariation;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationBuilderSpec extends ObjectBehavior
{
    public function let(InnerVariationTypeRepository $innerVariationTypeRepository, VariantGroupRepository $variantGroupRepository)
    {
        $this->beConstructedWith($innerVariationTypeRepository, $variantGroupRepository);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationBuilder::class);
    }

    public function it_builds_a_mixed_variation_from_a_variant_group_combination(
        DestinationPim $destinationPim,
        $innerVariationTypeRepository,
        $variantGroupRepository
    )
    {
        $family = new Family(41, 'family_parent', []);
        $variationFamily = new Family(41, 'variation_family', []);

        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $variantGroups = new \ArrayObject([
            new VariantGroup('group_1', 1, 1),
            new VariantGroup('group_2', 1, 1)
        ]);

        $innerVariationTypeRepository->findOneForFamilyCode('family_parent', $destinationPim)->willReturn($innerVariationType);
        $variantGroupRepository->retrieveVariantGroups($destinationPim, ['group_1', 'group_2'])->willReturn($variantGroups);

        $this->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->shouldBeLike(new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            $variantGroups
        ));
    }

    public function it_returns_null_if_there_is_no_inner_variation_type(DestinationPim $destinationPim, $innerVariationTypeRepository)
    {
        $family = new Family(41, 'family_parent', []);
        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);

        $innerVariationTypeRepository->findOneForFamilyCode('family_parent', $destinationPim)->willReturn(null);

        $this->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->shouldReturn(null);
    }
}
