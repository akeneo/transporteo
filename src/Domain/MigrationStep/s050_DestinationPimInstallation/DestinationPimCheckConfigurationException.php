<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

/**
 * Exception thrown when the destination PIM does not suit the Source PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimCheckConfigurationException extends \Exception
{
}
