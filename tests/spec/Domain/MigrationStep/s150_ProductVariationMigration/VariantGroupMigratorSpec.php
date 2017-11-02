<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupFamilyCreator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupMigrationCleaner;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupRemover;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupValidator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupMigratorSpec extends ObjectBehavior
{
    public function let(
        VariantGroupRetriever $variantGroupRetriever,
        VariantGroupRemover $variantGroupRemover,
        VariantGroupValidator $variantGroupValidator,
        VariantGroupFamilyCreator $familyCreator,
        VariantGroupProductMigrator $productMigrator,
        VariantGroupMigrationCleaner $variantGroupMigrationCleaner,
        MixedVariationMigrator $mixedVariationMigrator,
        TableMigrator $tableMigrator
    )
    {
        $this->beConstructedWith($variantGroupRetriever, $variantGroupRemover, $variantGroupValidator, $familyCreator, $productMigrator, $variantGroupMigrationCleaner, $mixedVariationMigrator, $tableMigrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VariantGroupMigrator::class);
    }

    public function it_migrates_successfully_all_variant_groups_without_mixed_variation(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRetriever,
        $variantGroupValidator,
        $familyCreator,
        $productMigrator,
        $mixedVariationMigrator,
        $tableMigrator
    )
    {
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $firstVariantGroup = new VariantGroup('vg_1', 1, 1);
        $secondVariantGroup = new VariantGroup('vg_2', 1, 1);
        $thirdVariantGroup = new VariantGroup('vg_3', 2, 1);
        $variantGroups = new \ArrayObject([$firstVariantGroup, $secondVariantGroup, $thirdVariantGroup]);

        $variantGroupRetriever->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($firstVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($secondVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($thirdVariantGroup, $destinationPim)->willReturn(true);

        $variantGroupCombinations = [
            ['family_code' => 'family_1', 'axes' => 'att_1', 'groups' => 'vg_1,vg_2'],
            ['family_code' => 'family_1', 'axes' => 'att_1,att_2', 'groups' => 'vg_3'],
            ['family_code' => 'family_2', 'axes' => 'att_2', 'groups' => 'vg_4'],
        ];
        $firstVariantGroupCombination = new VariantGroupCombination('family_1', 'family_1_1', ['att_1'], ['vg_1', 'vg_2']);
        $secondVariantGroupCombination = new VariantGroupCombination('family_1', 'family_1_2', ['att_1', 'att_2'], ['vg_3']);
        $thirdVariantGroupCombination = new VariantGroupCombination('family_2', 'family_2_1', ['att_2'], ['vg_4']);

        $mixedVariationMigrator->migrate($firstVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(false);
        $mixedVariationMigrator->migrate($secondVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(false);
        $mixedVariationMigrator->migrate($thirdVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(false);

        $variantGroupRetriever->retrieveVariantGroupCombinations($destinationPim)->willReturn($variantGroupCombinations);
        $variantGroupValidator->isVariantGroupCombinationValid($firstVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($secondVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($thirdVariantGroupCombination, $destinationPim)->willReturn(true);

        $firstFamilyVariant = new FamilyVariant(1, 'family_variant_1', ['att_1'], []);
        $secondFamilyVariant = new FamilyVariant(2, 'family_variant_2', ['att_1', 'att_2'], []);
        $thirdFamilyVariant = new FamilyVariant(3, 'family_variant_3', ['att_3'], []);

        $familyCreator->createFamilyVariant($firstVariantGroupCombination, $destinationPim)->willReturn($firstFamilyVariant);
        $familyCreator->createFamilyVariant($secondVariantGroupCombination, $destinationPim)->willReturn($secondFamilyVariant);
        $familyCreator->createFamilyVariant($thirdVariantGroupCombination, $destinationPim)->willReturn($thirdFamilyVariant);

        $productMigrator->migrateProductModels($firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductModels($secondVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductModels($thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $productMigrator->migrateProductVariants($firstFamilyVariant, $firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($secondFamilyVariant, $secondVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($thirdFamilyVariant, $thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupRetriever->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(0);

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_invalid_variant_groups(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRetriever,
        $variantGroupValidator,
        $familyCreator,
        $productMigrator,
        $variantGroupRemover,
        $mixedVariationMigrator,
        $tableMigrator
    )
    {
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $validVariantGroup = new VariantGroup('valid_vg', 1, 1);
        $invalidVariantGroup = new VariantGroup('vg_too_many_axes', 6, 1);

        $variantGroups = new \ArrayObject([$validVariantGroup, $invalidVariantGroup]);

        $variantGroupRetriever->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($validVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($invalidVariantGroup, $destinationPim)->willReturn(false);

        $variantGroupRemover->remove('vg_too_many_axes', $destinationPim)->shouldBeCalled();

        $variantGroupCombinations = [
            ['family_code' => 'family_1', 'axes' => 'att_1', 'groups' => 'vg_1,vg_2'],
            ['family_code' => 'family_2', 'axes' => 'att_1', 'groups' => 'invalid_vg_1,invalid_vg_2'],
        ];
        $validVariantGroupCombination = new VariantGroupCombination('family_1', 'family_1_1', ['att_1'], ['vg_1', 'vg_2']);
        $invalidVariantGroupCombination = new VariantGroupCombination('family_2', 'family_2_1', ['att_1'], ['invalid_vg_1', 'invalid_vg_2']);

        $mixedVariationMigrator->migrate($validVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(false);

        $variantGroupRetriever->retrieveVariantGroupCombinations($destinationPim)->willReturn($variantGroupCombinations);
        $variantGroupValidator->isVariantGroupCombinationValid($validVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($invalidVariantGroupCombination, $destinationPim)->willReturn(false);

        $variantGroupRemover->remove('invalid_vg_1', $destinationPim)->shouldBeCalled();
        $variantGroupRemover->remove('invalid_vg_2', $destinationPim)->shouldBeCalled();

        $familyVariant = new FamilyVariant(1, 'family_variant_1', ['att_1'], []);

        $familyCreator->createFamilyVariant($validVariantGroupCombination, $destinationPim)->willReturn($familyVariant);
        $productMigrator->migrateProductModels($validVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($familyVariant, $validVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupRetriever->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(1);

        $this->shouldThrow(new InvalidVariantGroupException(1))->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_migrates_variant_groups_with_mixed_variation(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRetriever,
        $variantGroupValidator,
        $familyCreator,
        $productMigrator,
        $mixedVariationMigrator,
        $tableMigrator
    )
    {
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $firstVariantGroup = new VariantGroup('vg_1', 1, 1);
        $secondVariantGroup = new VariantGroup('vg_2', 1, 1);
        $thirdVariantGroup = new VariantGroup('vg_3', 2, 1);
        $variantGroups = new \ArrayObject([$firstVariantGroup, $secondVariantGroup, $thirdVariantGroup]);

        $variantGroupRetriever->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($firstVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($secondVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($thirdVariantGroup, $destinationPim)->willReturn(true);

        $variantGroupCombinations = [
            ['family_code' => 'family_1', 'axes' => 'att_1', 'groups' => 'vg_1,vg_2'],
            ['family_code' => 'family_1', 'axes' => 'att_1,att_2', 'groups' => 'vg_3'],
            ['family_code' => 'family_2', 'axes' => 'att_2', 'groups' => 'vg_4'],
        ];
        $firstVariantGroupCombination = new VariantGroupCombination('family_1', 'family_1_1', ['att_1'], ['vg_1', 'vg_2']);
        $mixedVariantGroupCombination = new VariantGroupCombination('family_1', 'family_1_2', ['att_1', 'att_2'], ['vg_3']);
        $thirdVariantGroupCombination = new VariantGroupCombination('family_2', 'family_2_1', ['att_2'], ['vg_4']);

        $mixedVariationMigrator->migrate($firstVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(false);
        $mixedVariationMigrator->migrate($mixedVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(true);
        $mixedVariationMigrator->migrate($thirdVariantGroupCombination, $sourcePim, $destinationPim)->willReturn(false);

        $variantGroupRetriever->retrieveVariantGroupCombinations($destinationPim)->willReturn($variantGroupCombinations);
        $variantGroupValidator->isVariantGroupCombinationValid($firstVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($mixedVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($thirdVariantGroupCombination, $destinationPim)->willReturn(true);

        $firstFamilyVariant = new FamilyVariant(1, 'family_variant_1', ['att_1'], []);
        $secondFamilyVariant = new FamilyVariant(2, 'family_variant_2', ['att_1', 'att_2'], []);
        $thirdFamilyVariant = new FamilyVariant(3, 'family_variant_3', ['att_3'], []);

        $familyCreator->createFamilyVariant($firstVariantGroupCombination, $destinationPim)->willReturn($firstFamilyVariant);
        $familyCreator->createFamilyVariant($thirdVariantGroupCombination, $destinationPim)->willReturn($thirdFamilyVariant);

        $productMigrator->migrateProductModels($firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductModels($thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $productMigrator->migrateProductVariants($firstFamilyVariant, $firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($thirdFamilyVariant, $thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupRetriever->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(0);

        $this->migrate($sourcePim, $destinationPim);
    }
}
