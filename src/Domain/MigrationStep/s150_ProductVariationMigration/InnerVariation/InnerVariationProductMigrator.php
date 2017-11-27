<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Migrates products data related to an InnerVariationType.
 *  - Creates a product model for each product having variations from the IVB
 *  - Transforms each product variation into a product variant.
 *  - Remove the products migrated as product models.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationProductMigrator
{
    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ProductModelBuilder */
    private $productModelBuilder;

    /** @var ProductModelRepository */
    private $productModelRepository;
    /** @var ProductVariantTransformer */
    private $productVariantTransformer;

    /** @var FamilyVariantRepository */
    private $familyVariantRepository;

    public function __construct(
        InnerVariationTypeRepository $innerVariationTypeRepository,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        ProductModelBuilder $builder,
        ProductModelRepository $productModelRepository,
        ProductVariantTransformer $productVariantTransformer,
        FamilyVariantRepository $familyVariantRepository
    ) {
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->productModelBuilder = $builder;
        $this->productModelRepository = $productModelRepository;
        $this->productVariantTransformer = $productVariantTransformer;
        $this->familyVariantRepository = $familyVariantRepository;
    }

    /**
     * Migrates the products data per family.
     */
    public function migrate(InnerVariationType $innerVariationType, Pim $pim): void
    {
        $innerVariationFamily = $innerVariationType->getVariationFamily();
        $parentFamilies = $this->innerVariationTypeRepository->getParentFamiliesHavingVariantProducts($innerVariationType, $pim);

        foreach ($parentFamilies as $parentFamily) {
            $familyVariantCode = $parentFamily->getCode().'_'.$innerVariationFamily->getCode();
            $familyVariant = $this->familyVariantRepository->findOneByCode($familyVariantCode, $pim);
            $this->migrateFamilyVariantProducts($familyVariant, $parentFamily, $innerVariationFamily, $pim);
        }
    }

    /**
     * Migrates the products (models and variants) for a given family variant.
     * The products models have to be created via import because of the tree data.
     * And then they're updated with Mysql for the attributes values and the creation date.
     */
    private function migrateFamilyVariantProducts(
        FamilyVariant $familyVariant,
        Family $parentFamily,
        Family $innerVariationFamily,
        Pim $pim
    ): void
    {
        $productsModels = $this->createProductModels($parentFamily, $innerVariationFamily, $familyVariant, $pim);

        foreach ($productsModels as $productModel) {
            $this->productModelRepository->updateRawValuesAndCreatedForProduct($productModel, $pim);
            $this->productVariantTransformer->transform($productModel, $familyVariant, $parentFamily, $innerVariationFamily, $pim);
            $this->productRepository->delete($productModel->getIdentifier(), $pim);
        }
    }

    /**
     * Creates the product models for a given family and family variant.
     */
    private function createProductModels(
        Family $parentFamily,
        Family $innerVariationFamily,
        FamilyVariant $familyVariant,
        Pim $pim
    ): array
    {
        $products = $this->productRepository->findAllHavingVariantsForIvb($parentFamily->getId(), $innerVariationFamily->getId(), $pim);

        $productsModels = [];
        foreach ($products as $product) {
            $productModel = $this->productModelBuilder->build($product, $familyVariant, $pim);
            $productsModels[] = $this->productModelRepository->persist($productModel, $pim);
        }

        return $productsModels;
    }
}
