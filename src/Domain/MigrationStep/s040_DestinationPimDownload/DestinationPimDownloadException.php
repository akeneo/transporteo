<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;
use Throwable;

/**
 * Exception when downloading a PIM..
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloadException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 4 - Download Destination PIM : %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
