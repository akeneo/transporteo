<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception thrown when one or many variant groups are invalid.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InvalidVariantGroupException extends MigrationStepException
{
    public function __construct(int $numberOfRemovedInvalidVariantGroups)
    {
        $message = <<<EOT
There are $numberOfRemovedInvalidVariantGroups variant groups that can't be automatically migrated. Related products have been migrated but they're not variant.
Your catalog structure should be rework, according to the catalog modeling introduced in v2.0
See the file 'var/logs/error.log' for more details.
EOT;
        parent::__construct($message);
    }
}
