<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\CommandLauncher;

/**
 * Factory for destination PIM system requirements checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimSystemRequirementsCheckerFactory
{
    public function createCliDestinationPimSystemRequirementsChecker(CommandLauncher $commandLauncher): CliDestinationPimSystemRequirementsChecker
    {
        return new CliDestinationPimSystemRequirementsChecker($commandLauncher);
    }
}
