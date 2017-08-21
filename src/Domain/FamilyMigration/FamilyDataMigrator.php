<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\FamilyMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Migrator for family.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyDataMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $tableMigrator;

    /** @var DatabaseQueryExecutor */
    private $databaseQueryExecutor;

    public function __construct(TableMigrator $tableMigrator, DatabaseQueryExecutor $databaseQueryExecutor)
    {
        $this->tableMigrator = $tableMigrator;
        $this->databaseQueryExecutor = $databaseQueryExecutor;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $tableName = 'pim_catalog_family';

        $sqlAddColumnPart = 'ADD COLUMN image_attribute_id INT(11) DEFAULT NULL AFTER label_attribute_id';
        $sqlAddAttributeFkPart = 'ADD CONSTRAINT `FK_90632072BC295696` FOREIGN KEY (`image_attribute_id`) REFERENCES `pim_catalog_attribute` (`id`) ON DELETE SET NULL';
        $sqlAddKeyPart = 'ADD KEY `IDX_90632072BC295696` (`image_attribute_id`)';

        try {
            $this->tableMigrator->migrate($sourcePim, $destinationPim, $tableName);

            $this->databaseQueryExecutor->execute(
                sprintf(
                    'ALTER TABLE %s.%s %s, %s, %s',
                    $destinationPim->getDatabaseName(),
                    $tableName,
                    $sqlAddColumnPart,
                    $sqlAddAttributeFkPart,
                    $sqlAddKeyPart
                ),
                $destinationPim
            );

            $this->tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_family_attribute');
            $this->tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_family_translation');
        } catch (\Exception $exception) {
            throw new FamilyMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
