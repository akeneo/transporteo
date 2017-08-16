<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DatabaseServices;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Ds\Vector;

/**
 * Migrate from one table to another without any changes.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface NaiveMigrator
{
    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void;
}
