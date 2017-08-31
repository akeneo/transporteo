<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\FamilyMigration;

use Akeneo\PimMigration\Domain\MigrationStepException;
use Throwable;

/**
 * Exception for the family table migration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyMigrationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 9 - Family Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
