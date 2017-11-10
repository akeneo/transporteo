<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;

/**
 * Migrates products variations (via IVB and variant-group).
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVariationMigrator implements DataMigrator
{
    const MAX_VARIANT_AXES = 5;

    const ALLOWED_AXIS_TYPES = [
        'pim_catalog_simpleselect',
        'pim_reference_data_simpleselect',
        'pim_catalog_metric',
        'pim_catalog_boolean',
    ];

    /** @var InnerVariationTypeMigrator */
    private $innerVariantTypeMigrator;

    /** @var VariantGroupMigrator */
    private $variantGroupMigrator;

    /** @var ChainedConsole */
    private $console;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ChainedConsole $console,
        InnerVariationTypeMigrator $innerVariantTypeMigrator,
        VariantGroupMigrator $variantGroupMigrator,
        VariantGroupRepository $variantGroupRepository,
        LoggerInterface $logger
    ) {
        $this->console = $console;
        $this->innerVariantTypeMigrator = $innerVariantTypeMigrator;
        $this->variantGroupMigrator = $variantGroupMigrator;
        $this->variantGroupRepository = $variantGroupRepository;
        $this->logger = $logger;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $warnings = [];

        if ($sourcePim->hasIvb()) {
            try {
                $this->innerVariantTypeMigrator->migrate($sourcePim, $destinationPim);
            } catch (InvalidInnerVariationTypeException $exception) {
                $warnings[] = $exception->getMessage();
            }
        } else {
            $this->logger->info('There is no InnerVariationType to migrate.');
        }

        $numberOfVariantGroups = $this->variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim);

        if ($numberOfVariantGroups > 0) {
            $this->logger->info(sprintf("There are %d variant groups to migrate", $numberOfVariantGroups));

            try {
                $this->variantGroupMigrator->migrate($sourcePim, $destinationPim);
            } catch (InvalidVariantGroupException $exception) {
                $warnings[] = $exception->getMessage();
            }
        } else {
            $this->logger->info("There are no variant groups to migrate");
        }

        if ($sourcePim->hasIvb() || $numberOfVariantGroups > 0) {
            $this->refreshElasticSearchIndexes($destinationPim);
        }

        if (!empty($warnings)) {
            throw new InvalidProductVariationException($warnings);
        }
    }

    private function refreshElasticSearchIndexes(DestinationPim $destinationPim)
    {
        $this->console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim);
        $this->console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim);
    }
}
