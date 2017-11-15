<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantCodeBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

/**
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantCodeBuilderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantCodeBuilder::class);
    }

    public function it_builds_a_short_family_variant_code(VariantGroupCombination $variantGroupCombination, Family $family)
    {
        $variantGroupCombination->getFamily()->willReturn($family);
        $family->getCode()->willReturn('the_family');

        $variantGroupCombination->getAxes()->willReturn(['axe_1', 'axe_2']);

        $this->buildFromVariantGroupCombination($variantGroupCombination)->shouldReturn('the_family_axe_1_axe_2');
    }

    public function it_builds_a_valid_family_variant_code_even_if_the_axes_codes_are_too_long(VariantGroupCombination $variantGroupCombination, Family $family)
    {
        $variantGroupCombination->getFamily()->willReturn($family);
        $family->getCode()->willReturn('the_family');

        $variantGroupCombination->getAxes()->willReturn(['axe_1', str_repeat('code_too_long', 10)]);

        $validCodeRegex = sprintf('/^the_family[\w]{1,%d}$/', (FamilyVariantCodeBuilder::MAX_LENGTH - strlen('the_family')));
        $this->buildFromVariantGroupCombination($variantGroupCombination)->shouldMatch($validCodeRegex);
    }

    public function it_builds_different_family_variant_codes_even_if_the_axes_codes_are_too_long(
        VariantGroupCombination $firstVariantGroupCombination,
        VariantGroupCombination $secondVariantGroupCombination,
        Family $family
    ) {
        $firstVariantGroupCombination->getFamily()->willReturn($family);
        $secondVariantGroupCombination->getFamily()->willReturn($family);
        $family->getCode()->willReturn('the_family');

        $firstVariantGroupCombination->getAxes()->willReturn([str_repeat('code_too_long', 10)]);
        $secondVariantGroupCombination->getAxes()->willReturn([str_repeat('code_too_long', 10), 'axe_2']);

        $firstCode = $this->buildFromVariantGroupCombination($firstVariantGroupCombination)->getWrappedObject();
        $secondCode = $this->buildFromVariantGroupCombination($secondVariantGroupCombination)->getWrappedObject();

        if ($firstCode === $secondCode) {
            throw new FailureException('The family variant codes are the same');
        }
    }
}
