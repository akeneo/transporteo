<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Infrastructure\Command\DestinationPimCommandLauncher;

/**
 * Factory to create System requirements installer.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimSystemRequirementsInstallerFactory
{
    public function createDockerPimSystemRequirementsInstaller(
        DestinationPimCommandLauncher $dockerDestinationPimCommandLauncher,
        DestinationPimCommandLauncher $basicDestinationPimCommandLauncher
    ): DockerDestinationPimSystemRequirementsInstaller {
        return new DockerDestinationPimSystemRequirementsInstaller($dockerDestinationPimCommandLauncher, $basicDestinationPimCommandLauncher);
    }

    public function createBasicPimSystemRequirementsInstaller(DestinationPimCommandLauncher $destinationPimCommandLauncher): BasicDestinationPimSystemRequirementsInstaller
    {
        return new BasicDestinationPimSystemRequirementsInstaller($destinationPimCommandLauncher);
    }
}
