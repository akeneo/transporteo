<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Run a SymfonyProcess locally.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalCommandExecutor implements CommandExecutor
{
    public function execute(string $command, ?string $path, bool $activateTty): UnixCommandResult
    {
        $process = new Process($command, $path);

        if ($activateTty) {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $process->setTty(true);
            }
        }

        $process->enableOutput();
        $process->setTimeout(2 * 3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $authorizedExitCodes = [
                129, // Hangup
                130, // Interrupt
            ];
            if (!in_array($e->getProcess()->getExitCode(), $authorizedExitCodes)) {
                throw new UnsuccessfulCommandException($process->getErrorOutput(), $e->getCode(), $e);
            }
        }

        return new UnixCommandResult($process->getExitCode(), $process->getOutput());
    }
}
