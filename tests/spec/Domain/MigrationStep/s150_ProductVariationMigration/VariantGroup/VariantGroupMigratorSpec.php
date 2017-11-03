<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyCreator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\MigrationCleaner;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupValidator;
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
        VariantGroupRepository $variantGroupRepository,
        VariantGroupValidator $variantGroupValidator,
        FamilyCreator $familyCreator,
        FamilyRepository $familyRepository,
        ProductMigrator $productMigrator,
        MigrationCleaner $variantGroupMigrationCleaner,
        TableMigrator $tableMigrator
    )
    {
        $this->beConstructedWith($variantGroupRepository, $variantGroupValidator, $familyCreator, $familyRepository, $productMigrator, $variantGroupMigrationCleaner, $tableMigrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VariantGroupMigrator::class);
    }

    public function it_migrates_successfully_all_variant_groups(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRepository,
        $variantGroupValidator,
        $familyCreator,
        $familyRepository,
        $productMigrator,
        $tableMigrator
    )
    {
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $firstVariantGroup = new VariantGroup('vg_1', 1, 1);
        $secondVariantGroup = new VariantGroup('vg_2', 1, 1);
        $thirdVariantGroup = new VariantGroup('vg_3', 2, 1);
        $variantGroups = new \ArrayObject([$firstVariantGroup, $secondVariantGroup, $thirdVariantGroup]);

        $variantGroupRepository->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($firstVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($secondVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($thirdVariantGroup, $destinationPim)->willReturn(true);

        $variantGroupCombinationsData = [
            ['family_code' => 'family_1', 'axes' => 'att_1', 'groups' => 'vg_1,vg_2'],
            ['family_code' => 'family_1', 'axes' => 'att_1,att_2', 'groups' => 'vg_3'],
            ['family_code' => 'family_2', 'axes' => 'att_2', 'groups' => 'vg_4'],
        ];

        $firstFamily = new Family(11, 'family_1', []);
        $secondFamily = new Family(12, 'family_2', []);

        $familyRepository->findByCode('family_1', $destinationPim)->willReturn($firstFamily);
        $familyRepository->findByCode('family_2', $destinationPim)->willReturn($secondFamily);

        $variantGroupRepository->retrieveGroupAttributes('vg_1', $destinationPim)->willReturn(['vg_att_1', 'vg_att_2']);
        $variantGroupRepository->retrieveGroupAttributes('vg_3', $destinationPim)->willReturn(['vg_att_1']);
        $variantGroupRepository->retrieveGroupAttributes('vg_4', $destinationPim)->willReturn(['vg_att_3', 'vg_att_4']);

        $firstVariantGroupCombination = new VariantGroupCombination($firstFamily, 'family_1_1', ['att_1'], ['vg_1', 'vg_2'], ['vg_att_1', 'vg_att_2']);
        $secondVariantGroupCombination = new VariantGroupCombination($firstFamily, 'family_1_2', ['att_1', 'att_2'], ['vg_3'], ['vg_att_1']);
        $thirdVariantGroupCombination = new VariantGroupCombination($secondFamily, 'family_2_1', ['att_2'], ['vg_4'], ['vg_att_3', 'vg_att_4']);

        $variantGroupRepository->retrieveVariantGroupCombinations($destinationPim)->willReturn($variantGroupCombinationsData);
        $variantGroupValidator->isVariantGroupCombinationValid($firstVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($secondVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($thirdVariantGroupCombination, $destinationPim)->willReturn(true);

        $firstFamilyVariant = new FamilyVariant(1, 'family_variant_1', 'family_1', ['att_1'], [], [], [], []);
        $secondFamilyVariant = new FamilyVariant(2, 'family_variant_2', 'family_1', ['att_1', 'att_2'], [], [], [], []);
        $thirdFamilyVariant = new FamilyVariant(3, 'family_variant_3', 'family_2', ['att_3'], [], [], [], []);

        $familyCreator->createFamilyVariant($firstVariantGroupCombination, $destinationPim)->willReturn($firstFamilyVariant);
        $familyCreator->createFamilyVariant($secondVariantGroupCombination, $destinationPim)->willReturn($secondFamilyVariant);
        $familyCreator->createFamilyVariant($thirdVariantGroupCombination, $destinationPim)->willReturn($thirdFamilyVariant);

        $productMigrator->migrateProductModels($firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductModels($secondVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductModels($thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $productMigrator->migrateProductVariants($firstFamilyVariant, $firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($secondFamilyVariant, $secondVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($thirdFamilyVariant, $thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(0);

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_invalid_variant_groups(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRepository,
        $variantGroupValidator,
        $familyCreator,
        $familyRepository,
        $productMigrator,
        $tableMigrator
    )
    {
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $validVariantGroup = new VariantGroup('valid_vg', 1, 1);
        $invalidVariantGroup = new VariantGroup('vg_too_many_axes', 6, 1);

        $variantGroups = new \ArrayObject([$validVariantGroup, $invalidVariantGroup]);

        $variantGroupRepository->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($validVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($invalidVariantGroup, $destinationPim)->willReturn(false);

        $variantGroupRepository->softlyRemoveVariantGroup('vg_too_many_axes', $destinationPim)->shouldBeCalled();

        $variantGroupCombinations = [
            ['family_code' => 'family_1', 'axes' => 'att_1', 'groups' => 'vg_1,vg_2'],
            ['family_code' => 'family_2', 'axes' => 'att_1', 'groups' => 'invalid_vg_1,invalid_vg_2'],
        ];

        $firstFamily = new Family(11, 'family_1', []);
        $secondFamily = new Family(12, 'family_2', []);

        $familyRepository->findByCode('family_1', $destinationPim)->willReturn($firstFamily);
        $familyRepository->findByCode('family_2', $destinationPim)->willReturn($secondFamily);

        $variantGroupRepository->retrieveGroupAttributes('vg_1', $destinationPim)->willReturn(['vg_att_1', 'vg_att_2']);
        $variantGroupRepository->retrieveGroupAttributes('invalid_vg_1', $destinationPim)->willReturn(['vg_att_1']);

        $validVariantGroupCombination = new VariantGroupCombination($firstFamily, 'family_1_1', ['att_1'], ['vg_1', 'vg_2'], ['vg_att_1', 'vg_att_2']);
        $invalidVariantGroupCombination = new VariantGroupCombination($secondFamily, 'family_2_1', ['att_1'], ['invalid_vg_1', 'invalid_vg_2'], ['vg_att_1']);

        $variantGroupRepository->retrieveVariantGroupCombinations($destinationPim)->willReturn($variantGroupCombinations);
        $variantGroupValidator->isVariantGroupCombinationValid($validVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($invalidVariantGroupCombination, $destinationPim)->willReturn(false);

        $variantGroupRepository->softlyRemoveVariantGroup('invalid_vg_1', $destinationPim)->shouldBeCalled();
        $variantGroupRepository->softlyRemoveVariantGroup('invalid_vg_2', $destinationPim)->shouldBeCalled();

        $familyVariant = new FamilyVariant(1, 'family_variant_1', 'family_1', ['att_1'], [], [], [], []);

        $familyCreator->createFamilyVariant($validVariantGroupCombination, $destinationPim)->willReturn($familyVariant);
        $productMigrator->migrateProductModels($validVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $productMigrator->migrateProductVariants($familyVariant, $validVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(1);

        $this->shouldThrow(new InvalidVariantGroupException(1))->during('migrate', [$sourcePim, $destinationPim]);
    }
}
