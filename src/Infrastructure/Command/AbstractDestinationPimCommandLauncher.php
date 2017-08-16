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
    /** @var ProcessLauncher */
    private $processLauncher;

    public function __construct(ProcessLauncher $processLauncher)
    {
        $this->processLauncher = $processLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public function runCommand(Command $command, DestinationPim $destinationPim): void
    {
        $process = new Process($this->getStringCommand($command), $destinationPim->getPath());

        $this->processLauncher->runProcess($process);
    }

    abstract protected function getStringCommand(Command $command): string;
}
