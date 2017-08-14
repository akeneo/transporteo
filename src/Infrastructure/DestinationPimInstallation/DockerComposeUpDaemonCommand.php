<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Infrastructure\Command\Command;

/**
 * DockerCompouse Up DaemonCommand.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerComposeUpDaemonCommand implements Command
{
    public function getCommand(): string
    {
        return 'docker-compose up -d';
    }
}
