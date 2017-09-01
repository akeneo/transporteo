<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s30_AccessVerification;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;
use Throwable;

/**
 * Exception thrown if the access to a pim is not possible..
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AccessException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 3 - Forbidden Access: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
