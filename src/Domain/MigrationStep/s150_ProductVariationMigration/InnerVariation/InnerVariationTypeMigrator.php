<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidInnerVariationTypeException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;

/**
 * Migrates data from the inner variation types (IVB) according to the new product variation model.
 *  - Only InnerVariationType with no more than 5 axes. All the axes must be compatibles.
 *  - Updates families and create families variants.
 *  - Creates the product models and update the products variants.
 *  - Cleans the deprecated data and the schema.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeMigrator implements DataMigrator
{
    /** @var LoggerInterface */
    private $logger;

    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var InnerVariationFamilyMigrator */
    private $innerVariationFamilyMigrator;

    /** @var InnerVariationProductMigrator */
    private $innerVariationProductMigrator;

    /** @var InnerVariationCleaner */
    private $innerVariationCleaner;

    /** @var InnerVariationTypeValidator */
    private $innerVariationTypeValidator;

    public function __construct(
        InnerVariationTypeRepository $innerVariationTypeRepository,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        InnerVariationProductMigrator $innerVariationProductMigrator,
        InnerVariationCleaner $innerVariationCleaner,
        InnerVariationTypeValidator $innerVariationTypeValidator,
        LoggerInterface $logger
    ) {
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->innerVariationFamilyMigrator = $innerVariationFamilyMigrator;
        $this->innerVariationProductMigrator = $innerVariationProductMigrator;
        $this->innerVariationCleaner = $innerVariationCleaner;
        $this->innerVariationTypeValidator = $innerVariationTypeValidator;
        $this->logger = $logger;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $innerVariationTypes = $this->innerVariationTypeRepository->findAll($destinationPim);
        $invalidInnerVariationTypes = [];

        foreach ($innerVariationTypes as $innerVariationType) {
            if ($this->innerVariationTypeValidator->canInnerVariationTypeBeMigrated($innerVariationType)) {
                $this->migrateInnerVariationType($innerVariationType, $destinationPim);
            } else {
                $invalidInnerVariationTypes[] = $innerVariationType;
            }
        }

        $this->innerVariationCleaner->deleteInvalidInnerVariationTypesProducts($invalidInnerVariationTypes, $destinationPim);
        $this->innerVariationCleaner->cleanInnerVariationTypes($innerVariationTypes, $destinationPim);

        if (!empty($invalidInnerVariationTypes)) {
            throw new InvalidInnerVariationTypeException();
        }
    }

    /**
     * Migrates a given InnerVariationType.
     */
    private function migrateInnerVariationType(InnerVariationType $innerVariationType, Pim $pim): void
    {
        $this->logger->debug('Migrate the InnerVariationType '.$innerVariationType->getCode());

        try {
            $this->innerVariationFamilyMigrator->migrate($innerVariationType, $pim);
            $this->innerVariationProductMigrator->migrate($innerVariationType, $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'The migration of the InnerVariationType %s has failed : %s', $innerVariationType->getCode(), $exception->getMessage()
            ));
        }
    }
}
