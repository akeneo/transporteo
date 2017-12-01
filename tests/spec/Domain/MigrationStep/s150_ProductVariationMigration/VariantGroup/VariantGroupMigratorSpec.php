<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\MigrationCleaner;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombinationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombinationRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupValidator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupMigratorSpec extends ObjectBehavior
{
    public function let(
        VariantGroupRepository $variantGroupRepository,
        VariantGroupValidator $variantGroupValidator,
        VariantGroupCombinationRepository $variantGroupCombinationRepository,
        VariantGroupCombinationMigrator $variantGroupCombinationMigrator,
        MigrationCleaner $variantGroupMigrationCleaner,
        LoggerInterface $logger
    )
    {
        $this->beConstructedWith($variantGroupRepository, $variantGroupValidator, $variantGroupCombinationRepository, $variantGroupCombinationMigrator, $variantGroupMigrationCleaner, $logger);
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
        $variantGroupCombinationRepository,
        $variantGroupCombinationMigrator,
        $variantGroupMigrationCleaner
    )
    {
        $firstVariantGroup = new VariantGroup('vg_1', 1, 1);
        $secondVariantGroup = new VariantGroup('vg_2', 1, 1);
        $thirdVariantGroup = new VariantGroup('vg_3', 2, 1);
        $variantGroups = new \ArrayObject([$firstVariantGroup, $secondVariantGroup, $thirdVariantGroup]);

        $variantGroupRepository->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($firstVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($secondVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($thirdVariantGroup, $destinationPim)->willReturn(true);

        $firstFamily = new Family(11, 'family_1', []);
        $secondFamily = new Family(12, 'family_2', []);

        $firstVariantGroupCombination = new VariantGroupCombination($firstFamily,['att_1'], ['vg_1', 'vg_2'], ['vg_att_1', 'vg_att_2']);
        $secondVariantGroupCombination = new VariantGroupCombination($firstFamily,['att_1', 'att_2'], ['vg_3'], ['vg_att_1']);
        $thirdVariantGroupCombination = new VariantGroupCombination($secondFamily,['att_2'], ['vg_4'], ['vg_att_3', 'vg_att_4']);

        $variantGroupCombinationRepository->findAll($destinationPim)->willReturn(new \ArrayObject([
            $firstVariantGroupCombination,
            $secondVariantGroupCombination,
            $thirdVariantGroupCombination
        ]));

        $variantGroupValidator->isVariantGroupCombinationValid($firstVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($secondVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($thirdVariantGroupCombination, $destinationPim)->willReturn(true);

        $variantGroupCombinationMigrator->migrate($firstVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $variantGroupCombinationMigrator->migrate($secondVariantGroupCombination, $destinationPim)->shouldBeCalled();
        $variantGroupCombinationMigrator->migrate($thirdVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupMigrationCleaner->removeDeprecatedData($destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(0);

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_invalid_variant_groups(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRepository,
        $variantGroupValidator,
        $variantGroupCombinationRepository,
        $variantGroupCombinationMigrator,
        $variantGroupMigrationCleaner
    )
    {
        $validVariantGroup = new VariantGroup('valid_vg', 1, 1);
        $invalidVariantGroup = new VariantGroup('vg_too_many_axes', 6, 1);

        $variantGroups = new \ArrayObject([$validVariantGroup, $invalidVariantGroup]);

        $variantGroupRepository->retrieveVariantGroups($destinationPim)->willReturn($variantGroups);
        $variantGroupValidator->isVariantGroupValid($validVariantGroup, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupValid($invalidVariantGroup, $destinationPim)->willReturn(false);

        $variantGroupRepository->removeSoftlyVariantGroup('vg_too_many_axes', $destinationPim)->shouldBeCalled();

        $firstFamily = new Family(11, 'family_1', []);
        $secondFamily = new Family(12, 'family_2', []);

        $validVariantGroupCombination = new VariantGroupCombination($firstFamily, ['att_1'], ['vg_1', 'vg_2'], ['vg_att_1', 'vg_att_2']);
        $invalidVariantGroupCombination = new VariantGroupCombination($secondFamily, ['att_1'], ['invalid_vg_1', 'invalid_vg_2'], ['vg_att_1']);

        $allCombinations = new \ArrayObject([$validVariantGroupCombination, $invalidVariantGroupCombination]);
        $validCombinations = new \ArrayObject([$validVariantGroupCombination]);

        $variantGroupCombinationRepository->findAll($destinationPim)->willReturn($allCombinations, $validCombinations);

        $variantGroupValidator->isVariantGroupCombinationValid($validVariantGroupCombination, $destinationPim)->willReturn(true);
        $variantGroupValidator->isVariantGroupCombinationValid($invalidVariantGroupCombination, $destinationPim)->willReturn(false);

        $variantGroupCombinationRepository->removeSoftly($invalidVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupCombinationMigrator->migrate($validVariantGroupCombination, $destinationPim)->shouldBeCalled();

        $variantGroupMigrationCleaner->removeDeprecatedData($destinationPim)->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim)->willReturn(1);

        $this->migrate($sourcePim, $destinationPim);
    }
}
