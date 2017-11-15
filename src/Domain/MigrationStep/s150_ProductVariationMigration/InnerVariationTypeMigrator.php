<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidInnerVariationTypeException;
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

    /** @var InnerVariationRetriever */
    private $innerVariationRetriever;

    /** @var InnerVariationFamilyMigrator */
    private $innerVariationFamilyMigrator;

    /** @var InnerVariationProductMigrator */
    private $innerVariationProductMigrator;

    /** @var InnerVariationCleaner */
    private $innerVariationCleaner;

    public function __construct(
        InnerVariationRetriever $innerVariationRetriever,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        InnerVariationProductMigrator $innerVariationProductMigrator,
        InnerVariationCleaner $innerVariationCleaner,
        LoggerInterface $logger
    ) {
        $this->innerVariationRetriever = $innerVariationRetriever;
        $this->innerVariationFamilyMigrator = $innerVariationFamilyMigrator;
        $this->innerVariationProductMigrator = $innerVariationProductMigrator;
        $this->logger = $logger;
        $this->innerVariationCleaner = $innerVariationCleaner;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $innerVariationTypes = $this->innerVariationRetriever->retrieveInnerVariationTypes($destinationPim);
        $invalidInnerVariationTypes = [];

        foreach ($innerVariationTypes as $innerVariationType) {
            if ($this->canInnerVariationTypeBeMigrated($innerVariationType)) {
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

    /**
     * Retrieves and validate the variation axes of an InnerVariationType.
     */
    private function canInnerVariationTypeBeMigrated(InnerVariationType $innerVariationType): bool
    {
        $axes = $innerVariationType->getAxes();

        if (count($axes) > ProductVariationMigrator::MAX_VARIANT_AXES) {
            $this->logger->warning(sprintf(
                'Unable to migrate the inner variation type %s because it has more than %d axes.',
                $innerVariationType->getCode(),
                ProductVariationMigrator::MAX_VARIANT_AXES
            ));

            return false;
        }

        foreach ($axes as $axe) {
            if (!in_array($axe['attribute_type'], ProductVariationMigrator::ALLOWED_AXIS_TYPES)) {
                $this->logger->warning(sprintf(
                    'Unable to migrate the inner variation type %s because it has an axe of type %s.',
                    $innerVariationType->getCode(),
                    $axe['attribute_type']
                ));

                return false;
            }
        }

        return true;
    }
}
