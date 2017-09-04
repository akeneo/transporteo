<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration;

use Throwable;

/**
 * Exception for the extra tables migration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigrationException extends \Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 12 - Extra Data Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
