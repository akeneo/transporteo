<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Destination Pim command launcher factory.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimCommandLauncherFactory
{
    public function createDockerComposeCommandLauncher(string $container): DockerComposeDestinationPimCommandLauncher
    {
        return new DockerComposeDestinationPimCommandLauncher($container);
    }

    public function createBasicDestinationPimCommandLauncher(): BasicDestinationPimCommandLauncher
    {
        return new BasicDestinationPimCommandLauncher();
    }
}
