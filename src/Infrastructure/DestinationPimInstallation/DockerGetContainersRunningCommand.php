<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Infrastructure\Command\Command;

/**
 * Get container name running command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerGetContainersRunningCommand implements Command
{
    public function getCommand(): string
    {
        return 'docker ps --format="{{.Names}}';
    }
}
