<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * CommandLauncher factory.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandLauncherFactory
{
    public function createDockerComposeCommandLauncher(string $container): DockerComposeCommandLauncher
    {
        return new DockerComposeCommandLauncher($container);
    }

    public function createBasicCommandLauncher(): BasicCommandLauncher
    {
        return new BasicCommandLauncher();
    }
}
