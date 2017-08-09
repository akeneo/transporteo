<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DestinationPimInstallation;

/**
 * Contract to check if the requirements of the PIM are good.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DestinationPimSystemRequirementsChecker
{
    public function check(DestinationPim $destinationPim): void;
}
