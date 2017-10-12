<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception thrown when one or many inner variation types are invalid.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InvalidInnerVariationTypeException extends MigrationStepException
{
    public function __construct()
    {
        parent::__construct(
            "Some inner variation types can't be automatically migrated. Related products have not been migrated yet."
            .PHP_EOL."Your catalog structure should be rework, according to the catalog modeling introduced in v2.0 (Authorized axes are attributes of type 'Simple select', 'Reference data simple select', 'Metric', 'Boolean' and maximum 5 attributes per variant level)"
            .PHP_EOL."See the file 'var/logs/error.log' for more details."
        );
    }
}
