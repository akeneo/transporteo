<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
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

    /** @var InnerVariationRetriever */
    private $innerVariationRetriever;

    public function __construct(ChainedConsole $console, InnerVariationRetriever $innerVariationRetriever, LoggerInterface $logger)
    {
        $this->console = $console;
        $this->innerVariationRetriever = $innerVariationRetriever;
        $this->logger = $logger;
    }

    /**
     * Cleans the InnerVariationType data in the destination PIM.
     *  - Deletes the deprecated families
     *  - Drops the IVB MySQL tables.
     */
    public function cleanInnerVariationTypes(array $innerVariationTypes, DestinationPim $pim): void
    {
        // Drop the tables before deleting families to avoid constraint issues.
        $this->dropInnerVariationTables($pim);

        foreach ($innerVariationTypes as $innerVariationType) {
            $this->deleteInnerVariationFamily($innerVariationType, $pim);
        }
    }

    /**
     * Deletes the products of the invalid InnerVariationType that could not been migrated.
     */
    public function deleteInvalidInnerVariationTypesProducts(array $invalidInnerVariationTypes, DestinationPim $pim): void
    {
        foreach ($invalidInnerVariationTypes as $invalidInnerVariationType) {
            $innerVariationFamily = $this->innerVariationRetriever->retrieveInnerVariationFamily($invalidInnerVariationType, $pim);
            $parentFamilies = $this->innerVariationRetriever->retrieveParentFamilies($invalidInnerVariationType, $pim);

            foreach ($parentFamilies as $family) {
                $products = $this->innerVariationRetriever->retrievesFamilyProductsHavingVariants($family->getId(), $innerVariationFamily->getId(), $pim);

                foreach ($products as $product) {
                    $this->deleteProduct($product['identifier'], $pim);
                }
            }
        }

        $productsVariants = $this->innerVariationRetriever->retrieveNotMigratedProductVariants($pim);
        foreach ($productsVariants as $productsVariant) {
            $this->deleteProduct($productsVariant['identifier'], $pim);
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

    private function deleteProduct(string $productCode, Pim $pim): void
    {
        $command = new DeleteProductCommand($productCode);

        try {
            $this->console->execute($command, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to delete the product %s : %s', $productCode, $exception->getMessage()
            ));
        }
    }
}
