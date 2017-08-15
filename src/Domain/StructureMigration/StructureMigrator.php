<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\FilesMigration\StructureMigrationException;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Structure migration `locale`, `currency`, `category`, `attribute_group`, `group_type`
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class StructureMigrator
{
    private $structureMigrators = [];

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        /** @var TableStructureMigrator $structureMigrator */
        foreach ($this->structureMigrators as $structureMigrator) {
            $structureMigrator->migrate($sourcePim, $destinationPim);
        }
    }

    public function addStructureMigrator(TableStructureMigrator $structureMigrator): void
    {
        $this->structureMigrators[] = $structureMigrator;
    }
}
