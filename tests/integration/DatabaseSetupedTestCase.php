<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration;

use Akeneo\Pim\AkeneoPimClientBuilder;
use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutorRegistry;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimApiClientBuilder;
use Akeneo\PimMigration\Domain\Pim\PimApiParameters;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Cli\ApiCommandExecutor;
use Akeneo\PimMigration\Infrastructure\Cli\LocalConsole;
use Akeneo\PimMigration\Infrastructure\Cli\LocalMySqlQueryExecutor;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Monolog\Logger;
use Psr\Log\NullLogger;

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
    protected $chainedConsole;

    public function setUp()
    {
        parent::setUp();

        $this->chainedConsole = new ChainedConsole();
        $this->chainedConsole->addConsole(new LocalConsole(new LocalMySqlQueryExecutor(), new ApiCommandExecutor(new PimApiClientBuilder()), new NullLogger()));

        $sourcePimConfig = $this->getConfig('pim_community_standard_one_dot_seven_with_reference_data');
        $destinationPimConfig = $this->getConfig('pim_community_standard_two_dot_zero');

        $apiParameters = new PimApiParameters('', '', '', '', '');

        $this->sourcePim = new SourcePim($sourcePimConfig['database_host'], $sourcePimConfig['database_port'], $sourcePimConfig['database_name'], $sourcePimConfig['database_user'], $sourcePimConfig['database_password'], null, null, false, null, false, $sourcePimConfig['absolute_path'], new Localhost(), $apiParameters);
        $this->destinationPim = new DestinationPim($destinationPimConfig['database_host'], $destinationPimConfig['database_port'], $destinationPimConfig['database_name'], $destinationPimConfig['database_user'], $destinationPimConfig['database_password'], false, null, $destinationPimConfig['absolute_path'], new Localhost(), $apiParameters);

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
