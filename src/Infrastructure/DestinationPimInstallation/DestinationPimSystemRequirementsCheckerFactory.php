<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\CliDestinationPimSystemRequirementsChecker;
use Akeneo\PimMigration\Infrastructure\Command\DestinationPimCommandLauncher;

/**
 * Factory for destination PIM system requirements checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimSystemRequirementsCheckerFactory
{
    public function createCliDestinationPimSystemRequirementsChecker(DestinationPimCommandLauncher $commandLauncher): CliDestinationPimSystemRequirementsChecker
    {
        return new CliDestinationPimSystemRequirementsChecker($commandLauncher);
    }
}
