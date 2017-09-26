<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\Command\Api\ListAllProductsCommand;
use Akeneo\PimMigration\Domain\Command\Api\UpsertListProductsCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Migrates the associations of products.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductAssociationMigrator implements DataMigrator
{
    /** @var int */
    private $batchSize;

    /** @var ChainedConsole */
    private $console;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(int $batchSize, ChainedConsole $console, ?LoggerInterface $logger)
    {
        $this->batchSize = $batchSize;
        $this->console = $console;
        $this->logger = $logger;

        if (null === $this->logger) {
            $this->logger = new NullLogger();
        }
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $command = new ListAllProductsCommand($this->batchSize);
        $products = $this->console->execute($command, $sourcePim)->getOutput();
        $productsWithAssociations = [];

        foreach ($products as $product) {
            if (!empty($product['associations'])) {
                $productsWithAssociations[] = [
                    'identifier' => $product['identifier'],
                    'associations' => $product['associations'],
                ];

                if ($this->batchSize === count($productsWithAssociations)) {
                    $this->upsertProducts($destinationPim, $productsWithAssociations);
                    $productsWithAssociations = [];
                }
            }
        }

        if (!empty($productsWithAssociations)) {
            $this->upsertProducts($destinationPim, $productsWithAssociations);
        }
    }

    private function upsertProducts(Pim $pim, array $products): void
    {
        $command = new UpsertListProductsCommand($products);
        $apiResponses = $this->console->execute($command, $pim)->getOutput();

        foreach ($apiResponses as $apiResponse) {
            if ($apiResponse['status_code'] >= 400) {
                $this->logger->warning(sprintf(
                    'Migration of the associations of the product %s : %s', $apiResponse['identifier'], $apiResponse['message']
                ));
            }
        }
    }
}
