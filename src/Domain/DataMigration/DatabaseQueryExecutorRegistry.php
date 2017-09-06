<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Registry which known where are located the pims and how to query them.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseQueryExecutorRegistry
{
    /** @var DatabaseQueryExecutor[] */
    private $databaseQueryExecutors = [];

    public function get(Pim $pim): DatabaseQueryExecutor
    {
        foreach ($this->databaseQueryExecutors as $databaseQueryExecutor) {
            if ($databaseQueryExecutor->supports($pim->getConnection())) {
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
