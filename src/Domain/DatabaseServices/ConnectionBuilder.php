<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DatabaseServices;

use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectionBuilder
{
    public function createConnection(AbstractPim $pim): Connection
    {
        $connection = DriverManager::getConnection($pim->getDatabaseConnectionParams(), new Configuration());
        $connection->connect();

        return $connection;
    }
}
