<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Command\CommandLauncher;
use Akeneo\PimMigration\Domain\Command\UnixCommandResult;

/**
 * Generic command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractCommandLauncher implements CommandLauncher
{
    /** @var CommandExecutor */
    private $commandExecutor;

    public function __construct(CommandExecutor $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
    }

    /**
     * {@inheritdoc}
     */
    public function runCommand(Command $command, ?string $path, bool $activateTty): UnixCommandResult
    {
        return $this->commandExecutor->execute($this->getStringCommand($command), $path, $activateTty);
    }

    abstract protected function getStringCommand(Command $command): string;
}
