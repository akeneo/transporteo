<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;
use Throwable;

/**
 * Exception thrown if we don't found a suitable PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimInstallationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 5 - Destination PIM installation: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
