<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Migration of groups data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GroupMigrator implements DataMigrator
{
    private $groupMigrators = [];

    /** @var DatabaseQueryExecutor */
    private $databaseQueryExecutor;

    public function __construct(DatabaseQueryExecutor $databaseQueryExecutor)
    {
        $this->databaseQueryExecutor = $databaseQueryExecutor;
    }

    public function addGroupMigrator(DataMigrator $groupMigrator): void
    {
        $this->groupMigrators[] = $groupMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            /** @var DataMigrator $groupMigrator */
            foreach ($this->groupMigrators as $groupMigrator) {
                $groupMigrator->migrate($sourcePim, $destinationPim);
            }

            $this->databaseQueryExecutor->execute(
                sprintf('UPDATE %s.pim_catalog_group_type SET is_variant = 0', $destinationPim->getDatabaseName()),
                $destinationPim
            );
        } catch (\Exception $exception) {
            throw new GroupMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
