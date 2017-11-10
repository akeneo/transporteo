<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InvalidVariantGroupException;
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

    /** @var FamilyVariantBuilder */
    private $familyCreator;

    /** @var ProductMigrator */
    private $productMigrator;

    /** @var VariantGroupCombinationRepository */
    private $variantGroupCombinationRepository;

    /** @var FamilyVariantRepository */
    private $familyVariantRepository;

    public function __construct(
        VariantGroupRepository $variantGroupRepository,
        VariantGroupValidator $variantGroupValidator,
        VariantGroupCombinationRepository $variantGroupCombinationRepository,
        FamilyVariantBuilder $familyCreator,
        FamilyVariantRepository $familyVariantRepository,
        ProductMigrator $productMigrator,
        MigrationCleaner $variantGroupMigrationCleaner,
        TableMigrator $tableMigrator
    ) {
        $this->variantGroupRepository = $variantGroupRepository;
        $this->tableMigrator = $tableMigrator;
        $this->variantGroupValidator = $variantGroupValidator;
        $this->variantGroupMigrationCleaner = $variantGroupMigrationCleaner;
        $this->variantGroupCombinationRepository = $variantGroupCombinationRepository;
        $this->familyCreator = $familyCreator;
        $this->productMigrator = $productMigrator;
        $this->familyVariantRepository = $familyVariantRepository;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->migrateDeprecatedTables($sourcePim, $destinationPim);
        $this->removeInvalidVariantGroups($destinationPim);
        $this->removeInvalidVariantGroupCombinations($destinationPim);

        $variantGroupCombinations = $this->variantGroupCombinationRepository->findAll($destinationPim);

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            $familyVariant = $this->familyCreator->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim);
            $familyVariant->persist($this->familyVariantRepository, $destinationPim);

            $this->productMigrator->migrateProductModels($variantGroupCombination, $familyVariant, $destinationPim);
            $this->productMigrator->migrateProductVariants($variantGroupCombination, $familyVariant, $destinationPim);
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
