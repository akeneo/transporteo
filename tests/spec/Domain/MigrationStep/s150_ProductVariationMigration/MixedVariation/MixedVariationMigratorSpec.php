<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidMixedVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\FamilyVariantBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariation;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationValidator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombinationRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationMigratorSpec extends ObjectBehavior
{
    public function let(
        FamilyVariantBuilder $familyVariantBuilder,
        FamilyVariantRepository $familyVariantRepository,
        MixedVariationProductMigrator $productMigrator,
        InnerVariationTypeRepository $innerVariationTypeRepository,
        MixedVariationBuilder $mixedVariationBuilder,
        MixedVariationValidator $mixedVariationValidator,
        VariantGroupRepository $variantGroupRepository,
        VariantGroupCombinationRepository $variantGroupCombinationRepository,
        LoggerInterface $logger
    )
    {
        $this->beConstructedWith(
            $familyVariantBuilder,
            $familyVariantRepository,
            $productMigrator,
            $innerVariationTypeRepository,
            $mixedVariationBuilder,
            $mixedVariationValidator,
            $variantGroupRepository,
            $variantGroupCombinationRepository,
            $logger
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationMigrator::class);
    }

    public function it_migrates_the_mixed_variations(
        $familyVariantBuilder,
        $familyVariantRepository,
        $productMigrator,
        $mixedVariationBuilder,
        $mixedVariationValidator,
        $variantGroupCombinationRepository,
        $innerVariationTypeRepository,
        $variantGroupRepository,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        FamilyVariant $familyVariant
    )
    {
        $parentFamily = new Family(20, 'a_family', []);
        $variationFamily = new Family(31, 'ivt_family', []);

        $mixedVariantGroupCombination = new VariantGroupCombination($parentFamily, ['axis_1'], ['group_1', 'group_2'], []);
        $notMixedVariantGroupCombination = new VariantGroupCombination($parentFamily, ['axis_2'], ['group_3', 'group_4'], []);

        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $firstVariantGroup = new VariantGroup('group_1', 1, 1);
        $secondVariantGroup = new VariantGroup('group_2', 1, 1);

        $firstProduct = new Product(1, 'product_1', 31, '2016-11-23 12:45:38', 'group_1');
        $seconProduct = new Product(2, 'product_2', 31, '2016-11-21 12:42:38', 'group_2');

        $mixedVariation = new MixedVariation(
            $mixedVariantGroupCombination,
            $innerVariationType,
            [$firstProduct, $seconProduct],
            new \ArrayObject([$firstVariantGroup, $secondVariantGroup])
        );

        $variantGroupCombinationRepository
            ->findAll($destinationPim)
            ->willReturn(new \ArrayObject([$mixedVariantGroupCombination, $notMixedVariantGroupCombination]));

        $mixedVariationBuilder->buildFromVariantGroupCombination($mixedVariantGroupCombination, $destinationPim)->willReturn($mixedVariation);
        $mixedVariationBuilder->buildFromVariantGroupCombination($notMixedVariantGroupCombination, $destinationPim)->willReturn(null);

        $mixedVariationValidator->isValid($mixedVariation, $destinationPim)->willReturn(true);
        $familyVariantBuilder->build($mixedVariation, $destinationPim)->willReturn($familyVariant);

        $familyVariantRepository->persist($familyVariant, $destinationPim)->willReturn($familyVariant);

        $productMigrator->migrateProducts($mixedVariation, $familyVariant, $destinationPim)->shouldBeCalled();

        $innerVariationTypeRepository->delete($innerVariationType, $destinationPim)->shouldBeCalled();

        $variantGroupRepository->removeSoftlyVariantGroup('group_1', $destinationPim)->shouldBeCalled();
        $variantGroupRepository->removeSoftlyVariantGroup('group_2', $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_invalid_mixed_variations(
        $variantGroupRepository,
        $innerVariationTypeRepository,
        $mixedVariationBuilder,
        $mixedVariationValidator,
        $variantGroupCombinationRepository,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $parentFamily = new Family(20, 'a_family', []);
        $variationFamily = new Family(31, 'ivt_family', []);

        $variantGroupCombination = new VariantGroupCombination($parentFamily, ['axis_1'], ['group_1', 'group_2'], []);

        $innerVariationType = new InnerVariationType(11, 'ivt_1', $variationFamily, ['axis_2']);

        $firstVariantGroup = new VariantGroup('group_1', 1, 1);
        $secondVariantGroup = new VariantGroup('group_2', 1, 1);

        $firstProduct = new Product(1, 'product_1', 31, '2016-11-23 12:45:38', 'group_1');
        $seconProduct = new Product(2, 'product_2', 31, '2016-11-21 12:42:38', 'group_2');

        $mixedVariation = new MixedVariation(
            $variantGroupCombination,
            $innerVariationType,
            [$firstProduct, $seconProduct],
            new \ArrayObject([$firstVariantGroup, $secondVariantGroup])
        );

        $variantGroupCombinationRepository
            ->findAll($destinationPim)
            ->willReturn(new \ArrayObject([$variantGroupCombination]));

        $mixedVariationBuilder->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim)->willReturn($mixedVariation);

        $mixedVariationValidator->isValid($mixedVariation, $destinationPim)->willReturn(false);

        $innerVariationTypeRepository->delete($innerVariationType, $destinationPim)->shouldBeCalled();

        $variantGroupRepository->removeSoftlyVariantGroup('group_1', $destinationPim)->shouldBeCalled();
        $variantGroupRepository->removeSoftlyVariantGroup('group_2', $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
