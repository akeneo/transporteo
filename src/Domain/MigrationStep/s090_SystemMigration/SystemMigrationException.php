<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;
use Throwable;

/**
 * Exception for the system tables migration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SystemMigrationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 10 - System Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
