<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupValidator;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupValidatorSpec extends ObjectBehavior
{
    public function let(VariantGroupRetriever $variantGroupRetriever, LoggerInterface $logger)
    {
        $this->beConstructedWith($variantGroupRetriever, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VariantGroupValidator::class);
    }

    public function it_validates_a_variant_group(VariantGroup $variantGroup)
    {
        $variantGroup->getNumberOfAxes()->willReturn(5);
        $variantGroup->getNumberOfFamilies()->willReturn(1);

        $this->isVariantGroupValid($variantGroup)->shouldReturn(true);
    }

    public function it_invalidates_a_variant_group_having_more_than_five_axes(VariantGroup $variantGroup, $logger)
    {
        $variantGroup->getCode()->willReturn('vg_1');
        $variantGroup->getNumberOfAxes()->willReturn(6);
        $logger->warning('Unable to migrate the variant-group vg_1 because it has more than 5 axes.')->shouldBeCalled();

        $this->isVariantGroupValid($variantGroup)->shouldReturn(false);
    }

    public function it_invalidates_a_variant_group_having_several_families(VariantGroup $variantGroup, $logger)
    {
        $variantGroup->getCode()->willReturn('vg_1');
        $variantGroup->getNumberOfAxes()->willReturn(5);
        $variantGroup->getNumberOfFamilies()->willReturn(2);
        $logger->warning('Unable to migrate the variant-group vg_1 because not all its products are of the same family.')->shouldBeCalled();

        $this->isVariantGroupValid($variantGroup)->shouldReturn(false);
    }

    public function it_validates_a_variant_group_combination($variantGroupRetriever, Pim $pim)
    {
        $variantGroupCombination = new VariantGroupCombination('family_1', 'family_variant_1', ['axe_1', 'axe_2'], ['group_1', 'group_2']);

        $variantGroupRetriever->retrieveFamilyAttributes('family_1', $pim)->willReturn(['att_1', 'att_2', 'att_3']);
        $variantGroupRetriever->retrieveGroupAttributes('group_1', $pim)->willReturn(['att_1', 'att_2']);
        $variantGroupRetriever->retrieveGroupAttributes('group_2', $pim)->willReturn(['att_1', 'att_2']);

        $this->isVariantGroupCombinationValid($variantGroupCombination, $pim)->shouldReturn(true);
    }

    public function it_invalidates_a_variant_group_combination_having_variant_groups_with_different_attributes(
        $variantGroupRetriever,
        $logger,
        Pim $pim
    )
    {
        $variantGroupCombination = new VariantGroupCombination('family_1', 'family_variant_1', ['axe_1', 'axe_2'], ['group_1', 'group_2']);

        $variantGroupRetriever->retrieveFamilyAttributes('family_1', $pim)->willReturn(['att_1', 'att_2', 'att_3']);
        $variantGroupRetriever->retrieveGroupAttributes('group_1', $pim)->willReturn(['att_1', 'att_2']);
        $variantGroupRetriever->retrieveGroupAttributes('group_2', $pim)->willReturn(['att_1', 'att_3']);

        $logger->warning(
            "Unable to migrate the variations for the family family_1 and axis axe_1, axe_2, because all the following variation group(s) don't have the same attributes : group_1, group_2"
        )->shouldBeCalled();

        $this->isVariantGroupCombinationValid($variantGroupCombination, $pim)->shouldReturn(false);
    }

    public function it_invalidates_a_variant_group_combination_if_a_variant_groups_has_an_attribute_that_does_not_belong_to_the_family(
        $variantGroupRetriever,
        $logger,
        Pim $pim
    )
    {
        $variantGroupCombination = new VariantGroupCombination('family_1', 'family_variant_1', ['axe_1', 'axe_2'], ['group_1', 'group_2']);

        $variantGroupRetriever->retrieveFamilyAttributes('family_1', $pim)->willReturn(['att_1', 'att_2']);
        $variantGroupRetriever->retrieveGroupAttributes('group_1', $pim)->willReturn(['att_1', 'att_3', 'att_4']);
        $variantGroupRetriever->retrieveGroupAttributes('group_2', $pim)->willReturn(['att_1', 'att_3', 'att_4']);

        $logger->warning(
            "Unable to migrate the variations for the family family_1 and axis axe_1, axe_2, because all the following attribute(s) of the variant groups don't belong to the family : att_3, att_4"
        )->shouldBeCalled();

        $this->isVariantGroupCombinationValid($variantGroupCombination, $pim)->shouldReturn(false);
    }
}