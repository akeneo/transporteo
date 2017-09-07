<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Command\MySqlExportTableCommand;
use Akeneo\PimMigration\Domain\Command\MySqlImportTableCommand;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Command\UnsuccessfulCommandException;

/**
 * Copy a table as it is using dump from the source PIM to the destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class TableMigrator
{
    /** @var FileFetcherRegistry */
    private $fileFetcherRegistry;

    /** @var ConsoleHelper */
    private $consoleHelper;

    public function __construct(ConsoleHelper $consoleHelper, FileFetcherRegistry $fileFetcherRegistry)
    {
        $this->fileFetcherRegistry = $fileFetcherRegistry;
        $this->consoleHelper = $consoleHelper;
    }

    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void {
        $exportPath = MySqlExportTableCommand::getPimTableNameDumpPath($sourcePim, $tableName);

        $exportCommand = new MySqlExportTableCommand($tableName, $exportPath);

        try {
            $this->consoleHelper->execute($sourcePim, $exportCommand);
        } catch (\Exception $exception) {
            throw new DataMigrationException(
                sprintf('Dump Table error %s: %s', $tableName, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        $this->fileFetcherRegistry->fetch($sourcePim->getConnection(), $exportPath, true);

        $importCommand = new MySqlImportTableCommand(MySqlImportTableCommand::getLocalTableDumpPath($tableName));

        try {
            $this->consoleHelper->execute($destinationPim, $importCommand);
        } catch (UnsuccessfulCommandException $exception) {
            throw new DataMigrationException(
                sprintf('Import Dump of table %s error: %s', $tableName, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
