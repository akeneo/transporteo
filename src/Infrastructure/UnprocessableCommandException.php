<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

/**
 * Runtime exception thrown when an ssh exec command is unable to process
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class UnprocessableCommandException extends \RuntimeException
{
}
