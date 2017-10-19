<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
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
    /** @var VariantGroupRetriever */
    private $variantGroupRetriever;

    /** @var TableMigrator */
    private $tableMigrator;

    /** @var VariantGroupRemover */
    private $variantGroupRemover;

    /** @var VariantGroupValidator */
    private $variantGroupValidator;

    /** @var VariantGroupCombinationMigrator */
    private $variantGroupCombinationMigrator;

    /** @var VariantGroupMigrationCleaner */
    private $variantGroupMigrationCleaner;

    public function __construct(
        VariantGroupRetriever $variantGroupRetriever,
        VariantGroupRemover $variantGroupRemover,
        VariantGroupValidator $variantGroupValidator,
        VariantGroupCombinationMigrator $variantGroupCombinationMigrator,
        VariantGroupMigrationCleaner $variantGroupMigrationCleaner,
        TableMigrator $tableMigrator
    ) {
        $this->variantGroupRetriever = $variantGroupRetriever;
        $this->tableMigrator = $tableMigrator;
        $this->variantGroupRemover = $variantGroupRemover;
        $this->variantGroupValidator = $variantGroupValidator;
        $this->variantGroupCombinationMigrator = $variantGroupCombinationMigrator;
        $this->variantGroupMigrationCleaner = $variantGroupMigrationCleaner;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute');
        $this->tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template');

        $this->removeInvalidVariantGroups($destinationPim);

        $variantGroupCombinations = $this->retrieveVariantGroupCombinationsToMigrate($destinationPim);

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            $this->variantGroupCombinationMigrator->migrate($variantGroupCombination, $destinationPim);
        }

        $this->variantGroupMigrationCleaner->clean($destinationPim);

        $numberOfRemovedInvalidVariantGroups = $this->variantGroupRetriever->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim);
        if ($numberOfRemovedInvalidVariantGroups > 0) {
            throw new InvalidVariantGroupException($numberOfRemovedInvalidVariantGroups);
        }
    }

    /**
     * Remove softly the invalid variant-groups from the migration by changing their type to a specific one.
     */
    private function removeInvalidVariantGroups(DestinationPim $pim): void
    {
        $variantGroups = $this->variantGroupRetriever->retrieveVariantGroups($pim);

        foreach ($variantGroups as $variantGroup) {
            if (!$this->variantGroupValidator->isVariantGroupValid($variantGroup, $pim)) {
                $this->variantGroupRemover->remove($variantGroup->getCode(), $pim);
            }
        }
    }

    private function retrieveVariantGroupCombinationsToMigrate(DestinationPim $pim): \Traversable
    {
        $variantGroupCombinations = $this->retrieveVariantGroupCombinations($pim);

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            if ($this->variantGroupValidator->isVariantGroupCombinationValid($variantGroupCombination, $pim)) {
                yield $variantGroupCombination;
            } else {
                $this->removeVariantGroupCombination($variantGroupCombination, $pim);
            }
        }
    }

    /**
     * Retrieves and build the variant groups combinations.
     */
    private function retrieveVariantGroupCombinations(DestinationPim $pim)
    {
        $variantGroupCombinations = $this->variantGroupRetriever->retrieveVariantGroupCombinations($pim);
        $familyIncrement = 1;
        $previousFamily = null;

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            if ($variantGroupCombination['family_code'] === $previousFamily) {
                ++$familyIncrement;
            } else {
                $familyIncrement = 1;
            }

            $previousFamily = $variantGroupCombination['family_code'];

            yield new VariantGroupCombination(
                (string) $variantGroupCombination['family_code'],
                $variantGroupCombination['family_code'].'_'.$familyIncrement,
                explode(',', $variantGroupCombination['axes']),
                explode(',', $variantGroupCombination['groups'])
            );
        }
    }

    private function removeVariantGroupCombination(VariantGroupCombination $variantGroupCombination, DestinationPim $pim): void
    {
        foreach ($variantGroupCombination->getGroups() as $groupCode) {
            $this->variantGroupRemover->remove($groupCode, $pim);
        }
    }
}
