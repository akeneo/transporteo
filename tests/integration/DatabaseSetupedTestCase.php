<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutorRegistry;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Cli\LocalConsole;
use Akeneo\PimMigration\Infrastructure\Cli\LocalMySqlQueryExecutor;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;

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
    protected $consoleHelper;

    public function setUp()
    {
        parent::setUp();

        $this->consoleHelper = new ConsoleHelper();
        $this->consoleHelper->addConsole(new LocalConsole(new LocalMySqlQueryExecutor()));

        $sourcePimConfig = $this->getConfig('pim_community_standard_one_seven_with_reference_data');
        $destinationPimConfig = $this->getConfig('pim_community_standard_two');

        $this->sourcePim = new SourcePim($sourcePimConfig['database_host'], $sourcePimConfig['database_port'], $sourcePimConfig['database_name'], $sourcePimConfig['database_user'], $sourcePimConfig['database_password'], null, null, false, null, false, $sourcePimConfig['absolute_path'], new Localhost());
        $this->destinationPim = new DestinationPim($destinationPimConfig['database_host'], $destinationPimConfig['database_port'], $destinationPimConfig['database_name'], $destinationPimConfig['database_user'], $destinationPimConfig['database_password'], false, null, 'akeneo_pim', 'localhost', $destinationPimConfig['absolute_path'], new Localhost());

        $connection = $this->getConnection($this->destinationPim, false);
        $connection->exec('DROP DATABASE IF EXISTS akeneo_pim_two_for_test');
        $connection->exec('CREATE DATABASE akeneo_pim_two_for_test');

    }

    protected function getConnection(Pim $pim, bool $useDb): \PDO {
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
