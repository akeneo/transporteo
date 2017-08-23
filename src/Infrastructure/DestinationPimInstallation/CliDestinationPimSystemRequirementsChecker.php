<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsChecker;
use Akeneo\PimMigration\Domain\Command\CommandLauncher;

/**
 * Check system requirements through CLI.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CliDestinationPimSystemRequirementsChecker implements DestinationPimSystemRequirementsChecker
{
    /** @var CommandLauncher */
    private $destinationPimCommandLauncher;

    public function __construct(CommandLauncher $destinationPimCommandLauncher)
    {
        $this->destinationPimCommandLauncher = $destinationPimCommandLauncher;
    }

    public function check(DestinationPim $destinationPim): void
    {
        $this->destinationPimCommandLauncher->runCommand(new CheckRequirementsCommand(), $destinationPim->getPath(), true);
    }
}
