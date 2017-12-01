<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Psr\Log\LoggerInterface;

/**
 * Validates the InnerVariationType migration rules.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeValidator
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function canInnerVariationTypeBeMigrated(InnerVariationType $innerVariationType): bool
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

        foreach ($axes as $axis) {
            if (!in_array($axis['attribute_type'], ProductVariationMigrator::ALLOWED_AXIS_TYPES)) {
                $this->logger->warning(sprintf(
                    'Unable to migrate the inner variation type %s because it has an axis of type %s.',
                    $innerVariationType->getCode(),
                    $axis['attribute_type']
                ));

                return false;
            }
        }

        return true;
    }
}
