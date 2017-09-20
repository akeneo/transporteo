<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s015_SourcePimApiConfiguration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception thrown if the API client of the source PIM could not be configured.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimApiConfigurationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Error: Step 1.5 - SourcePimApiConfiguration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
