<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration;

use Throwable;

/**
 * Enterprise edition Data Migration Exception.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionDataMigrationException extends \Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 12.5 - Enterprise Edition Data Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
