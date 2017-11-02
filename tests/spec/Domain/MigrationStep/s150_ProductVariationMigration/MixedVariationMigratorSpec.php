<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombination;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationMigratorSpec extends ObjectBehavior
{

    public function let(
        MixedVariationRetriever $mixedVariationRetriever,
        MixedVariationFamilyMigrator $familyMigrator,
        MixedVariationProductMigrator $productMigrator
    ) {
        $this->beConstructedWith($mixedVariationRetriever, $familyMigrator, $productMigrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationMigrator::class);
    }

    public function it_successfully_migrates_mixed_variation(
        $mixedVariationRetriever,
        $familyMigrator,
        $productMigrator,
        SourcePim $sourcePim,
        DestinationPim$destinationPim
    ) {
        $sourcePim->hasIvb()->willReturn(true);

        $variantGroupCombination = new VariantGroupCombination('family_parent', 'family_variant', ['axe_1'], ['group_1', 'group_2']);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', 32, ['axe_2']);

        $mixedVariationRetriever
            ->retrieveInnerVariationTypeByFamilyCode($variantGroupCombination->getFamilyCode(), $destinationPim)
            ->willReturn($innerVariationType);

        $firstProduct = new Product(1, 'product_1', 31, '2016-11-23 12:45:38', 'group_1');
        $seconProduct = new Product(2, 'product_2', 31, '2016-11-21 12:42:38', 'group_2');

        $mixedVariationRetriever
            ->retrieveProductsHavingVariantsByGroups($variantGroupCombination->getGroups(), $innerVariationType->getVariationFamilyId(), $destinationPim)
            ->willReturn([$firstProduct, $seconProduct]);

        $familyVariant = new FamilyVariant(14, 'real_family_variant', ['att_1', 'att_2'], ['att_3']);

        $familyMigrator
            ->migrateFamilyVariant($variantGroupCombination, $innerVariationType, $destinationPim)
            ->willReturn($familyVariant);

        $productMigrator->migrateLevelOneProductModels($variantGroupCombination, $destinationPim)->shouldBeCalled();

        $firstProductModel = new ProductModel(1, 'product_model_1', 31);
        $secondProductModel = new ProductModel(2, 'product_model_2', 31);

        $productMigrator
            ->migrateLevelTwoProductModels([$firstProduct, $seconProduct], $variantGroupCombination, $destinationPim)
            ->willReturn([$firstProductModel, $secondProductModel]);

        $productMigrator
            ->migrateInnerVariationTypeProductVariants($firstProductModel, $familyVariant, $innerVariationType, $destinationPim)
            ->shouldBeCalled();

        $productMigrator
            ->migrateInnerVariationTypeProductVariants($secondProductModel, $familyVariant, $innerVariationType, $destinationPim)
            ->shouldBeCalled();

        $productMigrator
            ->migrateRemainingProductVariants($familyVariant, $variantGroupCombination, $destinationPim)
            ->shouldBeCalled();

        $this->migrate($variantGroupCombination, $sourcePim, $destinationPim)->shouldReturn(true);
    }

    public function it_migrates_nothing_if_source_has_no_ivb(
        SourcePim $sourcePim,
        DestinationPim$destinationPim,
        VariantGroupCombination $variantGroupCombination
    ) {
        $sourcePim->hasIvb()->willReturn(false);

        $this->migrate($variantGroupCombination, $sourcePim, $destinationPim)->shouldReturn(false);
    }

    public function it_migrates_nothing_if_there_is_no_ivt(
        $mixedVariationRetriever,
        SourcePim $sourcePim,
        DestinationPim$destinationPim
    ) {
        $sourcePim->hasIvb()->willReturn(true);

        $variantGroupCombination = new VariantGroupCombination('family_parent', 'family_variant', ['axe_1'], ['group_1', 'group_2']);

        $mixedVariationRetriever
            ->retrieveInnerVariationTypeByFamilyCode($variantGroupCombination->getFamilyCode(), $destinationPim)
            ->shouldBeCalled()
            ->willReturn(null);

        $this->migrate($variantGroupCombination, $sourcePim, $destinationPim)->shouldReturn(false);
    }

    public function it_migrates_nothing_if_there_is_no_product_having_variants(
        $mixedVariationRetriever,
        SourcePim $sourcePim,
        DestinationPim$destinationPim
    ) {
        $sourcePim->hasIvb()->willReturn(true);

        $variantGroupCombination = new VariantGroupCombination('family_parent', 'family_variant', ['axe_1'], ['group_1', 'group_2']);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', 32, ['axe_2']);

        $mixedVariationRetriever
            ->retrieveInnerVariationTypeByFamilyCode($variantGroupCombination->getFamilyCode(), $destinationPim)
            ->willReturn($innerVariationType);

        $mixedVariationRetriever
            ->retrieveProductsHavingVariantsByGroups($variantGroupCombination->getGroups(), $innerVariationType->getVariationFamilyId(), $destinationPim)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->migrate($variantGroupCombination, $sourcePim, $destinationPim)->shouldReturn(false);
    }
}
