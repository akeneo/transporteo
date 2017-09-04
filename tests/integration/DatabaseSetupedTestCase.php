<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\AbstractPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PHPUnit\Framework\TestCase;

/**
 * Abstract Test case preparing database.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class DatabaseSetupedTestCase extends ConfiguredTestCase
{
    protected $sourcePim;
    protected $destinationPim;

    public function setUp()
    {
        parent::setUp();

        $sourcePimConfig = $this->getConfig('pim_community_standard_one_seven_with_reference_data');
        $destinationPimConfig = $this->getConfig('pim_community_standard_two');

        $this->sourcePim = new SourcePim($sourcePimConfig['database_host'], $sourcePimConfig['database_port'], $sourcePimConfig['database_name'], $sourcePimConfig['database_user'], $sourcePimConfig['database_password'], null, null, false, null, false, '/a-path');
        $this->destinationPim = new DestinationPim($destinationPimConfig['database_host'], $destinationPimConfig['database_port'], $destinationPimConfig['database_name'], $destinationPimConfig['database_user'], $destinationPimConfig['database_password'], false, null, 'akeneo_pim', 'localhost', '/a-path');

        $connection = $this->getConnection($this->destinationPim, false);
        $connection->exec('DROP DATABASE IF EXISTS akeneo_pim_two_for_test');
        $connection->exec('CREATE DATABASE akeneo_pim_two_for_test');

    }

    protected function getConnection(AbstractPim $pim, bool $useDb): \PDO {
        $dsn = sprintf(
            'mysql: host=%s;port=%s',
            '127.0.0.1',
            $pim->getMysqlPort()
        );

        if ($useDb) {
            $dsn = $dsn.';dbname='.$pim->getDatabaseName();
        }

        $pdo =  new \PDO(
            $dsn,
            $pim->getDatabaseUser(),
            $pim->getDatabasePassword()
        );

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

}
