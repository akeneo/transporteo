<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Generic command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractDestinationPimCommandLauncher implements DestinationPimCommandLauncher
{
    /**
     * {@inheritdoc}
     */
    public function runCommand(Command $command, DestinationPim $destinationPim): Process
    {
        $process = new Process($this->getStringCommand($command), $destinationPim->getPath());

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $process->setTty(true);
        }

        $process->setTimeout(2 * 3600);
        $output = [];

        $process->enableOutput();

        try {
            $process->mustRun();
            $output[] = $process->getOutput();
//            $process->
        } catch (ProcessFailedException $e) {
            $authorizedExitCodes = [
                129, // Hangup
                130, // Interrupt
            ];
            if (!in_array($e->getProcess()->getExitCode(), $authorizedExitCodes)) {
                throw new UnsuccessfulCommandException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $process;
    }

    abstract protected function getStringCommand(Command $command): string;
}
