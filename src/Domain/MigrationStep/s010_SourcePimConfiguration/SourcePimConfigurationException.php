<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;
use Throwable;

/**
 * BusinessException for the step 1.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimConfigurationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Step 1 - SourcePimConfiguration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
