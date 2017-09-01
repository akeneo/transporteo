<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s60_FilesMigration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;
use Throwable;

/**
 * Exception for the files migration step.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FilesMigrationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 7 - Files Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
