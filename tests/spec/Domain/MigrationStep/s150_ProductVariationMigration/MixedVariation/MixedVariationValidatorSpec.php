<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeValidator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariation;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationValidator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupValidator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationValidatorSpec extends ObjectBehavior
{
    public function let(VariantGroupValidator $variantGroupValidator, InnerVariationTypeValidator $innerVariationTypeValidator)
    {
        $this->beConstructedWith($variantGroupValidator, $innerVariationTypeValidator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationValidator::class);
    }

    public function it_returns_true_if_the_mixed_migration_is_valid(
        DestinationPim $destinationPim,
        $variantGroupValidator,
        $innerVariationTypeValidator
    )
    {
        $family = new Family(41, 'family_parent', []);
        $variationFamily = new Family(41, 'variation_family', []);

        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $firstVariantGroup = new VariantGroup('group_1', 1, 1);
        $secondVariantGroup = new VariantGroup('group_2', 1, 1);

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            new \ArrayObject([$firstVariantGroup, $secondVariantGroup])
        );

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($innerVariationType)->willReturn(true);

        $variantGroupValidator->isVariantGroupValid($firstVariantGroup)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($secondVariantGroup)->willReturn(true);

        $variantGroupValidator->isVariantGroupCombinationValid($variantGroupCombination, $destinationPim)->willReturn(true);

        $this->isValid($mixedVariation, $destinationPim)->shouldReturn(true);
    }

    public function it_returns_false_if_the_inner_variation_type_is_invalid(DestinationPim $destinationPim, $innerVariationTypeValidator)
    {
        $family = new Family(41, 'family_parent', []);
        $variationFamily = new Family(41, 'variation_family', []);

        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            new \ArrayObject([])
        );

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($innerVariationType)->willReturn(false);

        $this->isValid($mixedVariation, $destinationPim)->shouldReturn(false);
    }

    public function it_returns_false_if_a_variant_group_is_invalid(
        DestinationPim $destinationPim,
        $variantGroupValidator,
        $innerVariationTypeValidator
    )
    {
        $family = new Family(41, 'family_parent', []);
        $variationFamily = new Family(41, 'variation_family', []);

        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $validVariantGroup = new VariantGroup('group_1', 1, 1);
        $invalidVariantGroup = new VariantGroup('group_2', 6, 1);

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            new \ArrayObject([$validVariantGroup, $invalidVariantGroup])
        );

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($innerVariationType)->willReturn(true);

        $variantGroupValidator->isVariantGroupValid($validVariantGroup)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($invalidVariantGroup)->willReturn(false);

        $this->isValid($mixedVariation, $destinationPim)->shouldReturn(false);
    }

    public function it_returns_false_if_the_variant_group_combination_is_invalid(
        DestinationPim $destinationPim,
        $variantGroupValidator,
        $innerVariationTypeValidator
    )
    {
        $family = new Family(41, 'family_parent', []);
        $variationFamily = new Family(41, 'variation_family', []);

        $variantGroupCombination = new VariantGroupCombination($family, ['vg_axis_1', 'vg_axis_2'], ['group_1', 'group_2'], []);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $firstVariantGroup = new VariantGroup('group_1', 1, 1);
        $secondVariantGroup = new VariantGroup('group_2', 1, 1);

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            new \ArrayObject([$firstVariantGroup, $secondVariantGroup])
        );

        $innerVariationTypeValidator->canInnerVariationTypeBeMigrated($innerVariationType)->willReturn(true);

        $variantGroupValidator->isVariantGroupValid($firstVariantGroup)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($secondVariantGroup)->willReturn(true);

        $variantGroupValidator->isVariantGroupCombinationValid($variantGroupCombination, $destinationPim)->willReturn(false);

        $this->isValid($mixedVariation, $destinationPim)->shouldReturn(false);
    }
}
