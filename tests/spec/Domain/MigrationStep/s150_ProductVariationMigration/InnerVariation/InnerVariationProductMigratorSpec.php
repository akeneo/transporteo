<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\ProductModelBuilder;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\ProductVariantTransformer;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationProductMigratorSpec extends ObjectBehavior
{
    function let(
        InnerVariationTypeRepository $innerVariationTypeRepository,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        ProductModelBuilder $productModelBuilder,
        ProductModelRepository $productModelRepository,
        ProductVariantTransformer $productVariantTransformer,
        FamilyVariantRepository $familyVariantRepository
    )
    {
        $this->beConstructedWith(
            $innerVariationTypeRepository,
            $logger,
            $productRepository,
            $productModelBuilder,
            $productModelRepository,
            $productVariantTransformer,
            $familyVariantRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationProductMigrator::class);
    }

    function it_successfully_migrates_products(
        $innerVariationTypeRepository,
        $productModelRepository,
        $familyVariantRepository,
        $productRepository,
        $productModelBuilder,
        $productVariantTransformer,
        InnerVariationType $innerVariationType,
        DestinationPim $pim
    )
    {
        $innerVariationFamily = new Family(1, 'inner_variation_family', []);
        $parentFamily = new Family(10, 'first_parent_family', []);

        $innerVariationType->getVariationFamily()->willReturn($innerVariationFamily);
        $innerVariationTypeRepository->getParentFamiliesHavingVariantProducts($innerVariationType, $pim)->willReturn(new \ArrayObject([$parentFamily]));

        $familyVariant = new FamilyVariant(20, 'first_family_variant');
        $familyVariantRepository->findOneByCode('first_parent_family_inner_variation_family', $pim)->willReturn($familyVariant);

        $product1 = new Product(110, 'product_model_1', null, null, null);

        $productRepository->findAllHavingVariantsForIvb(10, 1, $pim)->willReturn(new \ArrayObject([$product1]));

        $productRepository->getCategoryCodes(110, $pim)->willReturn(['cat_1', 'cat_2']);

        $productModel1 = new ProductModel(
            null,
            'product_model_1',
            'first_family_variant',
            ['cat_1', 'cat_2'],
            []
        );
        $productModelBuilder->build($product1, $familyVariant, $pim)->willReturn($productModel1);

        $persistedProductModel1 = new ProductModel(
            41,
            'product_model_1',
            'first_family_variant',
            ['cat_1', 'cat_2'],
            []
        );
        $productModelRepository->persist($productModel1, $pim)->willReturn($persistedProductModel1);

        $productModelRepository->updateRawValuesAndCreatedForProduct($persistedProductModel1, $pim)->shouldBeCalled();

        $productVariantTransformer->transform(
            $persistedProductModel1,
            $familyVariant,
            $parentFamily,
            $innerVariationFamily,
            $pim
        )->shouldBeCalled();

        $productRepository->delete($productModel1->getIdentifier(), $pim)->shouldBeCalled();

        $this->migrate($innerVariationType, $pim);
    }
}
