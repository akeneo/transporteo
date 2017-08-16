<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\ProcessLauncher;
use Symfony\Component\Process\Process;

/**
 * Copy a table as it is using dump from the source PIM to the destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DumpNaiveMigrator implements NaiveMigrator
{
    /** @var ProcessLauncher */
    private $processLauncher;

    public function __construct(ProcessLauncher $processLauncher)
    {
        $this->processLauncher = $processLauncher;
    }

    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void
    {
        $dumpPath = sprintf(
            '%s%s..%s..%s..%svar%s%s.sql',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $tableName
        );

        $dumpTableCommand = new DumpTableCommand($sourcePim, $tableName, $dumpPath);
        $this->processLauncher->runProcess(new Process($dumpTableCommand->getCommand()));

        //TODO TEMP MUST BE THINKING
        $importCommand = new ImportDockerDumpCommand($destinationPim, $dumpPath);
        $this->processLauncher->runProcess(new Process($importCommand->getCommand()));
    }
}
