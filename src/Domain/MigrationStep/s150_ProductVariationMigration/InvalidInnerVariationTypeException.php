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
            'Not all the inner variation types could be migrated. Their products have not been migrated either. See the file "var/logs/error.log" for more details.'
        );
    }
}
