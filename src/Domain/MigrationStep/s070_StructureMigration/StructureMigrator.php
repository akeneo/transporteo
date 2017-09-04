<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Structure migration `locale`, `currency`, `category`, `attribute_group`, `group_type`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class StructureMigrator
{
    private $structureMigrators = [];

    /**
     * @throws StructureMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        /** @var DataMigrator $structureMigrator */
        foreach ($this->structureMigrators as $structureMigrator) {
            try {
                $structureMigrator->migrate($sourcePim, $destinationPim);
            } catch (DataMigrationException $exception) {
                throw new StructureMigrationException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
    }

    public function addStructureMigrator(DataMigrator $structureMigrator): void
    {
        $this->structureMigrators[] = $structureMigrator;
    }
}
