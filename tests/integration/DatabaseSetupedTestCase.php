<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use PHPUnit\Framework\TestCase;

/**
 * Abstract Test case preparing database.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class DatabaseSetupedTestCase extends TestCase
{
    protected $sourcePim;
    protected $destinationPim;

    public function setUp()
    {
        parent::setUp();

        $this->sourcePim = new SourcePim('localhost', 3306, 'akeneo_pim_one_seven_for_test', 'akeneo_pim', 'akeneo_pim', null, null, false, null, false);
        $this->destinationPim = new DestinationPim('localhost', 3306, 'akeneo_pim_two_for_test', 'akeneo_pim', 'akeneo_pim', false, null, 'akeneo_pim', 'localhost', '/a-path');

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
