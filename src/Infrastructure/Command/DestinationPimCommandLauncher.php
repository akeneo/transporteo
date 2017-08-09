<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;

/**
 * Define public contract for a command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DestinationPimCommandLauncher
{
    /**
     * @throws UnsuccessfulCommandException
     */
    public function runCommand(Command $command, DestinationPim $destinationPim): void;
}
