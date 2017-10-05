<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\Command\Api\ListAllProductsCommand;
use Akeneo\PimMigration\Domain\Command\Api\UpsertListProductsCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Migrates the products data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMigrator implements DataMigrator
{
    /** @var int */
    private $batchSize;

    /** @var ChainedConsole */
    private $console;

    /** @var DataMigrator */
    private $productAssociationMigrator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        int $batchSize,
        ChainedConsole $console,
        DataMigrator $productAssociationMigrator,
        ?LoggerInterface $logger
    ) {
        $this->batchSize = $batchSize;
        $this->console = $console;
        $this->productAssociationMigrator = $productAssociationMigrator;
        $this->logger = $logger;

        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->migrateProducts($sourcePim, $destinationPim);

        $this->productAssociationMigrator->migrate($sourcePim, $destinationPim);
    }

    private function migrateProducts(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $command = new ListAllProductsCommand($this->batchSize);
        $products = $this->console->execute($command, $sourcePim)->getOutput();
        $productsToMigrate = [];

        foreach ($products as $product) {
            $productsToMigrate[] = $this->cleanProductForMigration($product);

            if ($this->batchSize === count($productsToMigrate)) {
                $this->upsertProducts($destinationPim, $productsToMigrate);
                $this->migrateProductsCreationDate($destinationPim, $productsToMigrate);
                $productsToMigrate = [];
            }
        }

        if (!empty($productsToMigrate)) {
            $this->upsertProducts($destinationPim, $productsToMigrate);
            $this->migrateProductsCreationDate($destinationPim, $productsToMigrate);
        }
    }

    private function upsertProducts(Pim $pim, array $products): void
    {
        $command = new UpsertListProductsCommand($products);
        $apiResponses = $this->console->execute($command, $pim)->getOutput();

        foreach ($apiResponses as $apiResponse) {
            if ($apiResponse['status_code'] >= 400) {
                $this->logger->warning(sprintf(
                    'Migration of the product %s : %s', $apiResponse['identifier'], $apiResponse['message']
                ));
            }
        }
    }

    /**
     * Cleans product data from the source PIM that cannot be migrated as it is.
     */
    private function cleanProductForMigration(array $product): array
    {
        // Variant groups no longer exist in 2.0 so they're migrated as normal groups.
        if (!empty($product['variant_group'])) {
            $product['groups'][] = $product['variant_group'];
        }

        unset($product['variant_group']);

        // Associations can not be defined until all products have been migrated.
        unset($product['associations']);

        return $product;
    }

    /**
     * Updates the creation dates of the products in the destination so that they're the same as in the source.
     */
    private function migrateProductsCreationDate(Pim $pim, array $products): void
    {
        foreach ($products as $product) {
            $creationDate = new \DateTime($product['created']);

            $command = new MySqlExecuteCommand(sprintf(
                'UPDATE pim_catalog_product SET created = "%s" WHERE identifier = "%s"',
                $creationDate->format('Y-m-d H:i:s'),
                $product['identifier']
            ));

            $this->console->execute($command, $pim);
        }
    }
}
