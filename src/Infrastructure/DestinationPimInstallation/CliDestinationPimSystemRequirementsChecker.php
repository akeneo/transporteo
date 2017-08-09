<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Infrastructure\Command\DestinationPimCommandLauncher;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\CheckRequirementsCommand;

/**
 * Check system requirements through CLI.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CliDestinationPimSystemRequirementsChecker implements DestinationPimSystemRequirementsChecker
{
    /** @var DestinationPimCommandLauncher */
    private $destinationPimCommandLauncher;

    public function __construct(DestinationPimCommandLauncher $destinationPimCommandLauncher)
    {
        $this->destinationPimCommandLauncher = $destinationPimCommandLauncher;
    }

    public function check(DestinationPim $destinationPim): void
    {
        $this->destinationPimCommandLauncher->runCommand(new CheckRequirementsCommand(), $destinationPim);
    }
}
