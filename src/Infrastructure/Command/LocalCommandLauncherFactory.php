<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Local command launcher factory.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalCommandLauncherFactory
{
    public function createDockerComposeCommandLauncher(string $container): DockerComposeCommandLauncher
    {
        return new DockerComposeCommandLauncher(new LocalCommandExecutor(), $container);
    }

    public function createBasicDestinationPimCommandLauncher(): BasicCommandLauncher
    {
        return new BasicCommandLauncher(new LocalCommandExecutor());
    }

    public function createDockerCommandLauncher(string $container): DockerCommandLauncher
    {
        return new DockerCommandLauncher(new LocalCommandExecutor(), $container);
    }
}
