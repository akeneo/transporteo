<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\CommandLauncher;

/**
 * Factory to create System requirements installer.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimSystemRequirementsInstallerFactory
{
    public function createDockerPimSystemRequirementsInstaller(CommandLauncher $commandLauncher): DockerDestinationPimSystemRequirementsInstaller
    {
        return new DockerDestinationPimSystemRequirementsInstaller($commandLauncher);
    }
}
