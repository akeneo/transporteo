<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;

/**
 * Allow to see differences in two databases.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseComparator extends Command
{
    /** @var Container */
    private $container;

    public function __construct(Container $container, $name = null)
    {
        $this->container = $container;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('akeneo-pim:database-comparator')
            ->setDescription('Compare two databases/tables from two databases')
            ->setHelp('You need a local database 1.7 named akeneo_pim and a local database 2.0 named -akeneo_pim_last_version_complete')
            ->addArgument('table_name', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $tableName = $input->getArgument('table_name');

        $sourcePimConnection = $this->getConnection('localhost', 'akeneo_pim');
        $destinationPimConnection = $this->getConnection('localhost', 'akeneo_pim_last_version_complete');

        if (null !== $tableName) {
            $this->compareTable($sourcePimConnection, $destinationPimConnection, $tableName, $io);

            return;
        }

        $sourceTables = $this->getTables($sourcePimConnection);
        $destinationTables = $this->getTables($destinationPimConnection);

        $diffDestinationToSource = array_diff($destinationTables, $sourceTables);
        $diffSourceToDestination = array_diff($sourceTables, $destinationTables);

        if (count($diffDestinationToSource) > 0) {
            $io->warning('Differences tables present in destination but not in sources');
            foreach ($diffDestinationToSource as $diff) {
                $io->writeln('NOT PRESENT IN SOURCE = '.$diff);
            }
        }

        if (count($diffSourceToDestination) > 0) {
            $io->warning('Differences tables present in source but not in destination');
            foreach ($diffSourceToDestination as $diff) {
                $io->writeln('NOT PRESENT IN DESTINATION = '.$diff);
            }
        }

        $commonTables = array_intersect($sourceTables, $destinationTables);

        foreach ($commonTables as $commonTable) {
            $this->compareTable($sourcePimConnection, $destinationPimConnection, $commonTable, $io);
        }
    }

    private function compareTable(
        \PDO $sourcePimConnection,
        \PDO $destinationPimConnection,
        string $tableName,
        SymfonyStyle $io
    ): void {
        $sourcePimColumns = $this->getAllColumns($sourcePimConnection, $tableName);
        $destinationPimColumns = $this->getAllColumns($destinationPimConnection, $tableName);

        $diffDestinationToSource = array_diff($destinationPimColumns, $sourcePimColumns);
        $diffSourceToDestination = array_diff($sourcePimColumns, $destinationPimColumns);

        if (count($diffSourceToDestination) > 0 || count($diffDestinationToSource) > 0) {
            $io->note('TABLE '.$tableName);
        }

        if (count($diffDestinationToSource) > 0) {
            $io->warning('Differences columns present in destination but not in source');
            foreach ($diffDestinationToSource as $diff) {
                $io->writeln('NOT PRESENT IN SOURCE = '.$diff);
            }
        }

        if (count($diffSourceToDestination) > 0) {
            $io->warning('Differences columns present in source but not in destination');
            foreach ($diffSourceToDestination as $diff) {
                $io->writeln('NOT PRESENT IN DESTINATION = '.$diff);
            }
        }
    }

    private function getAllColumns(\PDO $connection, string $tableName): array
    {
        $q = $connection->prepare(sprintf('DESCRIBE %s', $tableName));
        $q->execute();
        $rawResults = $q->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($rawResult) {
            return array_reduce(array_keys($rawResult), function ($carry, $key) use ($rawResult) {
                return $carry.'#'.$key.'='.$rawResult[$key];
            }, '');
        }, $rawResults);
    }

    private function getTables(\PDO $connection): array
    {
        $q = $connection->prepare('SHOW TABLES');
        $q->execute();

        return $q->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getConnection(
        string $host,
        string $databaseName,
        int $port = 3306,
        string $user = 'akeneo_pim',
        string $password = 'akeneo_pim'): \PDO
    {
        $dsn = sprintf(
            'mysql: host=%s;dbname=%s;port=%s',
            $host,
            $databaseName,
            strval($port)
        );

        $pdo = new \PDO(
            $dsn,
            $user,
            $password
        );

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
