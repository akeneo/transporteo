<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\StructureMigration;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Migrate a structure table.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface TableStructureMigrator
{
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void;
}
