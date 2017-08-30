<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\GroupMigration;

use Akeneo\PimMigration\Domain\MigrationStepException;
use Throwable;

/**
 * Exception for the Group tables migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GroupMigrationException extends MigrationStepException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = sprintf('Error: Group Migration: %s', $message);

        parent::__construct($message, $code, $previous);
    }
}
