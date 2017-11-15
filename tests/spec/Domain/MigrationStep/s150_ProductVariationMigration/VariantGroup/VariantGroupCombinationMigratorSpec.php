<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\FamilyVariantBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductModelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\ProductVariantTransformer;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombinationMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombinationMigratorSpec extends ObjectBehavior
{

    public function let(
        ProductModelRepository $productModelRepository,
        ProductModelBuilder $productModelBuilder,
        ProductVariantTransformer $productVariantTransformer,
        FamilyVariantRepository $familyVariantRepository,
        FamilyVariantBuilder $familyVariantBuilder
    )
    {
        $this->beConstructedWith($productModelRepository, $productModelBuilder, $productVariantTransformer, $familyVariantRepository, $familyVariantBuilder);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VariantGroupCombinationMigrator::class);
    }

    public function it_migrates_product_variations_from_a_variant_group_combination(
        $productModelRepository,
        $productModelBuilder,
        $productVariantTransformer,
        $familyVariantRepository,
        $familyVariantBuilder,
        FamilyVariant $familyVariant,
        VariantGroupCombination $variantGroupCombination,
        DestinationPim $pim,
        ProductModel $firstProductModel,
        ProductModel $secondProductModel
    )
    {
        $familyVariantBuilder->buildFromVariantGroupCombination($variantGroupCombination, $pim)->willReturn($familyVariant);
        $familyVariantRepository->persist($familyVariant, $pim)->willReturn($familyVariant);

        $variantGroupCombination->getGroups()->willReturn(['vg_1', 'vg_2']);

        $productModelBuilder->buildFromVariantGroup('vg_1', $familyVariant, $pim)->willReturn($firstProductModel);
        $productModelBuilder->buildFromVariantGroup('vg_2', $familyVariant, $pim)->willReturn($secondProductModel);

        $productModelRepository->persist($firstProductModel, $pim)->willReturn($firstProductModel);
        $productModelRepository->persist($secondProductModel, $pim)->willReturn($secondProductModel);

        $productVariantTransformer->transformFromProductModel($firstProductModel, $familyVariant, $pim)->shouldBeCalled();
        $productVariantTransformer->transformFromProductModel($secondProductModel, $familyVariant, $pim)->shouldBeCalled();

        $this->migrate($variantGroupCombination, $pim);
    }
}
