<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception for the product migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMigrationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Error: Step 14 - Product Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
