<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\Command\UnsuccessfulCommandException;

/**
 * Copy a table as it is using dump from the source PIM to the destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DumpTableMigrator implements TableMigrator
{
    /** @var LocalCommandLauncherFactory */
    private $commandLauncherFactory;

    public function __construct(LocalCommandLauncherFactory $commandLauncherFactory)
    {
        $this->commandLauncherFactory = $commandLauncherFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void {
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

        //TODO WE'LL HAVE TO CONNECT TO SSH
        $basicCommandLauncher = $this->commandLauncherFactory->createBasicDestinationPimCommandLauncher();
        $dumpTableCommand = new DumpTableCommand($sourcePim, $tableName, $dumpPath);

        try {
            $basicCommandLauncher->runCommand($dumpTableCommand, null);
        } catch (UnsuccessfulCommandException $exception) {
            throw new DataMigrationException(
                'Dump Table error: '.$exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        $importCommand = new ImportDumpCommand($destinationPim, $dumpPath);

        try {
            $basicCommandLauncher->runCommand($importCommand, null);
        } catch (UnsuccessfulCommandException $exception) {
            throw new DataMigrationException(
                'Dump Table error: '.$exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }
}
