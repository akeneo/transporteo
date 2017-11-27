<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Cleaning for the InnerVariationType migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationCleaner
{
    /** @var ChainedConsole */
    private $console;

    /** @var LoggerInterface */
    private $logger;

    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var ProductRepository */
    private $productRepository;

    public function __construct(
        ChainedConsole $console,
        InnerVariationTypeRepository $innerVariationRepository,
        LoggerInterface $logger,
        ProductRepository $productRepository
    )
    {
        $this->console = $console;
        $this->innerVariationTypeRepository = $innerVariationRepository;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    /**
     * Cleans the InnerVariationType data in the destination PIM.
     *  - Deletes the deprecated families
     *  - Drops the IVB MySQL tables.
     *  - Delete the attribute "variation_parent_product".
     */
    public function cleanInnerVariationTypes(array $innerVariationTypes, DestinationPim $pim): void
    {
        // Drop the tables before deleting families to avoid constraint issues.
        $this->dropInnerVariationTables($pim);

        foreach ($innerVariationTypes as $innerVariationType) {
            $this->deleteInnerVariationFamily($innerVariationType, $pim);
        }

        $this->deleteInnerVariationAttribute($pim);
    }

    /**
     * Deletes the products of the invalid InnerVariationType that could not been migrated.
     */
    public function deleteInvalidInnerVariationTypesProducts(array $invalidInnerVariationTypes, DestinationPim $pim): void
    {
        foreach ($invalidInnerVariationTypes as $invalidInnerVariationType) {
            $innerVariationFamily = $invalidInnerVariationType->getVariationFamily();
            $parentFamilies = $this->innerVariationTypeRepository->getParentFamiliesHavingVariantProducts($invalidInnerVariationType, $pim);

            foreach ($parentFamilies as $family) {
                $products = $this->productRepository->findAllHavingVariantsForIvb($family->getId(), $innerVariationFamily->getId(), $pim);

                foreach ($products as $product) {
                    $this->productRepository->delete($product->getIdentifier(), $pim);
                }
            }
        }

        $productsVariants = $this->productRepository->findAllNotMigratedProductVariants($pim);
        foreach ($productsVariants as $productsVariant) {
            $this->productRepository->delete($productsVariant->getIdentifier(), $pim);
        }
    }

    private function deleteInnerVariationFamily(InnerVariationType $innerVariationType, Pim $pim): void
    {
        $deleteFamilyCommand = new MySqlExecuteCommand('DELETE FROM pim_catalog_family WHERE id = '.$innerVariationType->getVariationFamilyId());

        try {
            $this->console->execute($deleteFamilyCommand, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to delete the family %s : %s', $innerVariationType->getVariationFamilyId(), $exception->getMessage()
            ));
        }
    }

    private function dropInnerVariationTables(Pim $pim): void
    {
        $dropSecondaryTablesCommand = new MySqlExecuteCommand(
            'DROP TABLE
                pim_inner_variation_inner_variation_type_axis,
                pim_inner_variation_inner_variation_type_family,
                pim_inner_variation_inner_variation_type_translation'
        );

        // This table must dropped last because of the constraints on columns.
        $dropMainTableCommand = new MySqlExecuteCommand('DROP TABLE pim_inner_variation_inner_variation_type');

        try {
            $this->console->execute($dropSecondaryTablesCommand, $pim);
            $this->console->execute($dropMainTableCommand, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning('Unable to drop all the InnerVariationType tables : '.$exception->getMessage());
        }
    }

    /**
     * Delete the attribute "variation_parent_product" specific to the IVB.
     */
    private function deleteInnerVariationAttribute(Pim $pim)
    {
        $deleteAttributeCommand = new MySqlExecuteCommand(
            'DELETE FROM pim_catalog_attribute WHERE code = "variation_parent_product"'
        );

        try {
            $this->console->execute($deleteAttributeCommand, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to delete the attribute variation_parent_product : %s', $exception->getMessage()
            ));
        }
    }
}
