<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\CreateProductModelCommand;
use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\Api\GetProductCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombination;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Migrates products according to the migration of mixed variant group and IVB.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationProductMigrator
{
    /** @var ChainedConsole */
    private $console;

    /** @var VariantGroupProductMigrator */
    private $variantGroupProductMigrator;

    /** @var MixedVariationRetriever */
    private $mixedVariationRetriever;

    public function __construct(
        ChainedConsole $console,
        VariantGroupProductMigrator $variantGroupProductMigrator,
        MixedVariationRetriever $mixedVariationRetriever
    )
    {
        $this->console = $console;
        $this->variantGroupProductMigrator = $variantGroupProductMigrator;
        $this->mixedVariationRetriever = $mixedVariationRetriever;
    }

    public function migrateLevelOneProductModels(VariantGroupCombination $variantGroupCombination, Pim $pim): void
    {
        $this->variantGroupProductMigrator->migrateProductModels($variantGroupCombination, $pim);
    }

    public function migrateLevelTwoProductModels(array $parentProducts, VariantGroupCombination $variantGroupCombination, Pim $pim): array
    {
        foreach ($parentProducts as $parentProduct) {
            $parentProductData = $this->console->execute(new GetProductCommand($parentProduct->getIdentifier()), $pim)->getOutput();

            $productModelData = [
                'family_variant' => $variantGroupCombination->getFamilyVariantCode(),
                'categories' => $parentProductData['categories'],
                'parent' => $parentProduct->getVariantGroupCode(),
                'values' => $parentProductData['values'],
            ];

            $this->console->execute(new CreateProductModelCommand($parentProduct->getIdentifier(), $productModelData), $pim);
        }

        $createdProductModels = [];
        foreach ($parentProducts as $parentProduct) {
            $productModelId = $this->mixedVariationRetriever->retrieveProductModelId($parentProduct->getIdentifier(), $pim);

            if (null === $productModelId) {
                throw new ProductVariationMigrationException(sprintf('Unable to retrieve the product model %s. It seems that its creation failed.', $parentProduct->getIdentifier()));
            }

            $this->console->execute(new DeleteProductCommand($parentProduct->getIdentifier()), $pim);

            $createdProductModels[] = new ProductModel($productModelId, $parentProduct->getIdentifier(), $parentProduct->getFamilyId());
        }

        return $createdProductModels;
    }

    public function migrateInnerVariationTypeProductVariants(ProductModel $productModel, FamilyVariant $familyVariant, InnerVariationType $innerVariationType, $pim)
    {
        $command = new MySqlExecuteCommand(sprintf(
            'UPDATE pim_catalog_product '
            .' SET family_id = %s, product_model_id = %s, family_variant_id = %s, product_type = "variant_product",'
            .' raw_values = JSON_REMOVE(raw_values, \'$.variation_parent_product\')'
            .' WHERE family_id = %s'
            .' AND JSON_EXTRACT(raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = "%s"',
            $productModel->getFamilyId(),
            $productModel->getId(),
            $familyVariant->getId(),
            $innerVariationType->getVariationFamilyId(),
            $productModel->getIdentifier()
        ));

        $this->console->execute($command, $pim);
    }

    /**
     * Migrates products variants for the variant groups products that don't have variants via the IVB.
     * It's just like the variant group migration because all the products having variants via the IVB have been deleted before.
     */
    public function migrateRemainingProductVariants(
        FamilyVariant $familyVariant,
        VariantGroupCombination $variantGroupCombination,
        DestinationPim $pim
    ): void
    {
        $this->variantGroupProductMigrator->migrateProductVariants($familyVariant, $variantGroupCombination, $pim);
    }
}
