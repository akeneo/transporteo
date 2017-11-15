<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrates variant groups data according to the new product variation model.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupMigrator implements DataMigrator
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var TableMigrator */
    private $tableMigrator;

    /** @var VariantGroupValidator */
    private $variantGroupValidator;

    /** @var MigrationCleaner */
    private $variantGroupMigrationCleaner;

    /** @var VariantGroupCombinationRepository */
    private $variantGroupCombinationRepository;

    /** @var VariantGroupCombinationMigrator */
    private $variantGroupCombinationMigrator;

    public function __construct(
        VariantGroupRepository $variantGroupRepository,
        VariantGroupValidator $variantGroupValidator,
        VariantGroupCombinationRepository $variantGroupCombinationRepository,
        VariantGroupCombinationMigrator $variantGroupCombinationMigrator,
        MigrationCleaner $variantGroupMigrationCleaner,
        TableMigrator $tableMigrator
    ) {
        $this->variantGroupRepository = $variantGroupRepository;
        $this->tableMigrator = $tableMigrator;
        $this->variantGroupValidator = $variantGroupValidator;
        $this->variantGroupMigrationCleaner = $variantGroupMigrationCleaner;
        $this->variantGroupCombinationRepository = $variantGroupCombinationRepository;
        $this->variantGroupCombinationMigrator = $variantGroupCombinationMigrator;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->migrateDeprecatedTables($sourcePim, $destinationPim);
        $this->removeInvalidVariantGroups($destinationPim);
        $this->removeInvalidVariantGroupCombinations($destinationPim);

        $variantGroupCombinations = $this->variantGroupCombinationRepository->findAll($destinationPim);

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            $this->variantGroupCombinationMigrator->migrate($variantGroupCombination, $destinationPim);
        }

        $this->variantGroupMigrationCleaner->removeDeprecatedData($destinationPim);

        $numberOfRemovedInvalidVariantGroups = $this->variantGroupRepository->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim);
        if ($numberOfRemovedInvalidVariantGroups > 0) {
            throw new InvalidVariantGroupException($numberOfRemovedInvalidVariantGroups);
        }
    }

    /**
     * Migrates MySQL tables that no longer exists in PIM 2.0, but are used to retrieve the variant group combinations.
     */
    private function migrateDeprecatedTables(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute');
        $this->tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template');
    }

    private function removeInvalidVariantGroups(DestinationPim $pim): void
    {
        $variantGroups = $this->variantGroupRepository->retrieveVariantGroups($pim);

        foreach ($variantGroups as $variantGroup) {
            if (!$this->variantGroupValidator->isVariantGroupValid($variantGroup, $pim)) {
                $this->variantGroupRepository->removeSoftlyVariantGroup($variantGroup->getCode(), $pim);
            }
        }
    }

    private function removeInvalidVariantGroupCombinations(DestinationPim $pim): void
    {
        $variantGroupCombinations = $this->variantGroupCombinationRepository->findAll($pim);

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            if (!$this->variantGroupValidator->isVariantGroupCombinationValid($variantGroupCombination, $pim)) {
                $this->variantGroupCombinationRepository->removeSoftly($variantGroupCombination, $pim);
            }
        }
    }
}
