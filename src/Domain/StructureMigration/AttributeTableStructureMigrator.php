<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DatabaseServices\ConnectionBuilder;
use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Attribute table migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeTableStructureMigrator implements TableStructureMigrator
{
    /** @var ConnectionBuilder */
    private $connectionBuilder;

    /** @var NaiveMigrator */
    private $naiveMigrator;

    public function __construct(NaiveMigrator $naiveMigrator, ConnectionBuilder $connectionBuilder)
    {
        $this->connectionBuilder = $connectionBuilder;
        $this->naiveMigrator = $naiveMigrator;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $tableName = 'pim_catalog_attribute';

        $this->naiveMigrator->migrate($sourcePim, $destinationPim, $tableName);

        $connection = $this->connectionBuilder->createConnection($destinationPim);

        $connection->createQueryBuilder()
            ->update($tableName)
            ->set('backend_type', '"textarea"')
            ->where('backend_type = "text"')
            ->execute();

        $connection->createQueryBuilder()
            ->update($tableName)
            ->set('backend_type', '"text"')
            ->where('backend_type = "varchar"')
            ->execute();
    }
}
