<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception for the migration of the product variations.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVariationMigrationException extends MigrationStepException
{
    public function __construct(string $message)
    {
        parent::__construct(sprintf('Error: Step 15 - Product variation migration: %s', $message));
    }
}
