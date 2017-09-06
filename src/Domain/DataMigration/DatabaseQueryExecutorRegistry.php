<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\DestinationPimConnected;
use Akeneo\PimMigration\Domain\Pim\DestinationPimConnectionAware;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Pim\SourcePimConnected;
use Akeneo\PimMigration\Domain\Pim\SourcePimConnectionAware;

/**
 * Registry which known where are located the pims and how to query them.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseQueryExecutorRegistry implements SourcePimConnectionAware, DestinationPimConnectionAware
{
    use SourcePimConnected;
    use DestinationPimConnected;

    /** @var DatabaseQueryExecutor[] */
    private $databaseQueryExecutors = [];

    public function get(Pim $pim): DatabaseQueryExecutor
    {
        $connection = $pim instanceof SourcePim ? $this->sourcePimConnection : $this->destinationPimConnection;

        foreach ($this->databaseQueryExecutors as $databaseQueryExecutor) {
            if ($databaseQueryExecutor->supports($connection)) {
                return $databaseQueryExecutor;
            }
        }

        throw new \InvalidArgumentException('The connection is not supported by any database query executor');
    }

    public function addDatabaseQueryExecutor(DatabaseQueryExecutor $databaseQueryExecutor): void
    {
        $this->databaseQueryExecutors[] = $databaseQueryExecutor;
    }
}
