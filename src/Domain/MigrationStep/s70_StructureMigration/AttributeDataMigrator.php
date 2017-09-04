<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s70_StructureMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

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

        $sqlUpdate = 'UPDATE %s.%s SET backend_type = "%s" WHERE backend_type = "%s"';

        try {
            $this->tableMigrator->migrate($sourcePim, $destinationPim, $tableName);

            $this->databaseQueryExecutor->execute(
                sprintf($sqlUpdate, $destinationPim->getDatabaseName(), $tableName, 'textarea', 'text'),
                $destinationPim
            );

            $this->databaseQueryExecutor->execute(
                sprintf($sqlUpdate, $destinationPim->getDatabaseName(), $tableName, 'text', 'varchar'),
                $destinationPim
            );
        } catch (\Exception $exception) {
            throw new StructureMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
