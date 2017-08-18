<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Command launcher for a specific docker container.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerCommandLauncher extends AbstractCommandLauncher implements CommandLauncher
{
    /** @var string */
    private $containerName;

    public function __construct(LocalCommandExecutor $processLauncher, string $containerName)
    {
        parent::__construct($processLauncher);
        $this->containerName = $containerName;
    }

    protected function getStringCommand(Command $command): string
    {
        return sprintf('docker exec -i %s %s', $this->containerName, trim($command->getCommand()));
    }
}
