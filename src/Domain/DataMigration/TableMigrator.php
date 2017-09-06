<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

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
    /** @var DatabaseQueryExecutorRegistry */
    private $databaseQueryExecutorRegistry;

    /** @var FileFetcherRegistry */
    private $fileFetcherRegistry;

    public function __construct(DatabaseQueryExecutorRegistry $databaseQueryExecutorRegistry, FileFetcherRegistry $fileFetcherRegistry)
    {
        $this->databaseQueryExecutorRegistry = $databaseQueryExecutorRegistry;
        $this->fileFetcherRegistry = $fileFetcherRegistry;
    }

    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void {
        $sourceDatabaseQueryExecutory = $this->databaseQueryExecutorRegistry->get($sourcePim);
        $destinationPimQueryExecutor = $this->databaseQueryExecutorRegistry->get($destinationPim);

        try {
            $sourceDatabaseQueryExecutory->exportTable($tableName, $sourcePim);
        } catch (\Exception $exception) {
            throw new DataMigrationException(
                sprintf('Dump Table error %s: %s', $tableName, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        $this->fileFetcherRegistry->fetch($sourcePim, $sourceDatabaseQueryExecutory->getPimTableNameDumpPath($sourcePim, $tableName), true);

        try {
            $destinationPimQueryExecutor->importTable($tableName, $destinationPim);
        } catch (UnsuccessfulCommandException $exception) {
            throw new DataMigrationException(
                sprintf('Import Dump of table %s error: %s', $tableName, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
