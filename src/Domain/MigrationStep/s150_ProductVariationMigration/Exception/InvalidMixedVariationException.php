<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception thrown when some product variations involving IVB + VG both can't not be migrated.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InvalidMixedVariationException extends MigrationStepException
{
    public function __construct()
    {
        $message = <<<EOT
There are mixed variant groups and inner variation types that can't be automatically migrated. Related products have been migrated but they're not variant.
Your catalog structure should be rework, according to the catalog modeling introduced in v2.0
See the file 'var/logs/error.log' for more details.
EOT;
        parent::__construct($message);
    }
}
