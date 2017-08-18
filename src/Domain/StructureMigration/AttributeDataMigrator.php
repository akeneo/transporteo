<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Attribute table migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeDataMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $tableMigrator;

    /** @var DatabaseQueryExecutor */
    private $databaseQueryExecutor;

    public function __construct(TableMigrator $naiveMigrator, DatabaseQueryExecutor $executor)
    {
        $this->tableMigrator = $naiveMigrator;
        $this->databaseQueryExecutor = $executor;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $tableName = 'pim_catalog_attribute';

        try {
            $this->tableMigrator->migrate($sourcePim, $destinationPim, $tableName);
        } catch (DataMigrationException $exception) {
            throw new StructureMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        try {
            $this->databaseQueryExecutor->execute(
                sprintf('UPDATE %s SET backend_type = "textarea" WHERE backend_type = "text"', $tableName),
                $destinationPim
            );
        } catch (QueryException $exception) {
            throw new DataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        try {
            $this->databaseQueryExecutor->execute(
                sprintf('UPDATE %s SET backend_type = "text" WHERE backend_type = "varchar"', $tableName),
                $destinationPim
            );
        } catch (QueryException $exception) {
            throw new DataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
