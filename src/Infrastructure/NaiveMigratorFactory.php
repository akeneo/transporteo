<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\DatabaseServices\ConnectionBuilder;
use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;

/**
 * Factory for naive factory.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class NaiveMigratorFactory
{
    public function createNaiveMigrator(ConnectionBuilder $connectionBuilder): NaiveMigrator
    {
        return new NaiveMigrator($connectionBuilder);
    }
}
