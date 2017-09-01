<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;

/**
 * Migrate a structure table.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DataMigrator
{
    /**
     * @throws DataMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void;
}
