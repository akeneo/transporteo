<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
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

            $this->databaseQueryExecutor->execute(
                sprintf('UPDATE %s SET backend_type = "textarea" WHERE backend_type = "text"', $tableName),
                $destinationPim
            );

            $this->databaseQueryExecutor->execute(
                sprintf('UPDATE %s SET backend_type = "text" WHERE backend_type = "varchar"', $tableName),
                $destinationPim
            );
        } catch (\Exception $exception) {
            throw new StructureMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
