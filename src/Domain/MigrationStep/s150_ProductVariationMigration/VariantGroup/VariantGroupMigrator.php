<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;

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

    /** @var VariantGroupValidator */
    private $variantGroupValidator;

    /** @var MigrationCleaner */
    private $variantGroupMigrationCleaner;

    /** @var VariantGroupCombinationRepository */
    private $variantGroupCombinationRepository;

    /** @var VariantGroupCombinationMigrator */
    private $variantGroupCombinationMigrator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        VariantGroupRepository $variantGroupRepository,
        VariantGroupValidator $variantGroupValidator,
        VariantGroupCombinationRepository $variantGroupCombinationRepository,
        VariantGroupCombinationMigrator $variantGroupCombinationMigrator,
        MigrationCleaner $variantGroupMigrationCleaner,
        LoggerInterface $logger
    ) {
        $this->variantGroupRepository = $variantGroupRepository;
        $this->variantGroupValidator = $variantGroupValidator;
        $this->variantGroupMigrationCleaner = $variantGroupMigrationCleaner;
        $this->variantGroupCombinationRepository = $variantGroupCombinationRepository;
        $this->variantGroupCombinationMigrator = $variantGroupCombinationMigrator;
        $this->logger = $logger;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->removeInvalidVariantGroups($destinationPim);
        $this->removeInvalidVariantGroupCombinations($destinationPim);

        $variantGroupCombinations = $this->variantGroupCombinationRepository->findAll($destinationPim);

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            $this->variantGroupCombinationMigrator->migrate($variantGroupCombination, $destinationPim);
        }

        $this->variantGroupMigrationCleaner->removeDeprecatedData($destinationPim);

        $numberOfRemovedInvalidVariantGroups = $this->variantGroupRepository->retrieveNumberOfRemovedInvalidVariantGroups($destinationPim);

        if ($numberOfRemovedInvalidVariantGroups > 0) {
            $this->logger->warning(<<<EOT
There are $numberOfRemovedInvalidVariantGroups variant groups that can't be automatically migrated. Related products have been migrated but they're not variant.
Your catalog structure should be rework, according to the catalog modeling introduced in v2.0
EOT
            );
        }
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
