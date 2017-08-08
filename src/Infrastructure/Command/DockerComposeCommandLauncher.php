<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\Command;
use Akeneo\PimMigration\Domain\CommandLauncher;

/**
 * Launch command through docker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerComposeCommandLauncher extends AbstractCommandLauncher implements CommandLauncher
{
    /** @var string */
    private $container;

    public function __construct(string $container)
    {
        $this->container = trim($container);
    }

    protected function getStringCommand(Command $command): string
    {
        return escapeshellcmd(sprintf(
            'docker-compose exec %s %s',
            $this->container,
            trim($command->getCommand())
        ));
    }
}
