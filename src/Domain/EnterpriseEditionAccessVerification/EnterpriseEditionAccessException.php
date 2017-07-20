<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\MigrationStepException;

/**
 * Exception thrown if the access to the enterprise licence is not possible..
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionAccessException extends MigrationStepException
{
}
