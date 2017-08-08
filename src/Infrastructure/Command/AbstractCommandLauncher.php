<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\Command;
use Akeneo\PimMigration\Domain\CommandLauncher;
use Akeneo\PimMigration\Domain\UnsuccessfulCommandException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Generic command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractCommandLauncher implements CommandLauncher
{
    /**
     * {@inheritdoc}
     */
    public function runCommand(Command $command, DestinationPim $destinationPim): void
    {
        $process = new Process($this->getStringCommand($command), $destinationPim->getPath());

        $process = new Process($this->getStringCommand($command), $destinationPim->getPath());
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $process->setTty(true);
        }

        $process->setTimeout(2 * 3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $authorizedExitCodes = [
                129, // Hangup
                130, // Interrupt
            ];
            if (!in_array($e->getProcess()->getExitCode(), $authorizedExitCodes)) {
                throw new UnsuccessfulCommandException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    abstract protected function getStringCommand(Command $command): string;
}
