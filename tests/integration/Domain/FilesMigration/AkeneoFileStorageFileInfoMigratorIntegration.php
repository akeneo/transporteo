<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\FilesMigration;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\UnsuccessfulCommandException;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigratorIntegration extends TestCase
{
    private $pim20ContainerName;
    private $pim17ContainerName;

    public function setUp()
    {
        parent::setUp();

        $stepPath = realpath(ResourcesFileLocator::getStepFolder('step_six_migrate_akeneo_file_storage_file_info'));

        $commandRoot = 'docker run -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim -p %d:3306 -v %s:/tmp/mysqldumps -d mysql:%s';
        $initCommand = 'docker exec %s "/tmp/mysqldumps/%s"';

        $configs = [
            [
                'mysql_version' => '5.6',
                'port' => 3310,
                'init_script' => 'full_import_pim_1_7.sh',
                'variable_name' => 'pim17ContainerName'
            ],
            [
                'mysql_version' => 5.7,
                'port' => 3311,
                'init_script' => 'empty_import_pim_2_0.sh',
                'variable_name' => 'pim20ContainerName'
            ]
        ];

        foreach ($configs as $config) {
            $bootCommand = sprintf($commandRoot, $config['port'], $stepPath, $config['mysql_version']);
            $bootCommandResult = $this->runCommand($bootCommand);
            $variableName = $config['variable_name'];
            $this->$variableName = substr($bootCommandResult->getOutput(), 0, -2);
            sleep(10);
            $initCommandRoot = sprintf($initCommand, $this->$variableName, $config['init_script']);
            $this->runCommand($initCommandRoot);
            sleep(10);
        }
    }

    public function testItCopyTheAkeneoFileStorageFileInfoTable()
    {
        $sourcePim = new SourcePim('localhost', 3310, 'akeneo_pim', 'akeneo_pim', 'akeneo_pim', null, null, false, null, false);
        $destinationPim = new DestinationPim('localhost', 3311, 'akeneo_pim', 'akeneo_pim', 'akeneo_pim', false, null, 'akeneo_pim', 'localhost', '/a-path');

        $akeneoFileStorageFileInfoMigrator = new AkeneoFileStorageFileInfoMigrator();
        $akeneoFileStorageFileInfoMigrator->migrate($sourcePim, $destinationPim);

        $sourcePimConnection = DriverManager::getConnection($sourcePim->getDatabaseConnectionParams(), new Configuration());
        $destinationPimConnection = DriverManager::getConnection($destinationPim->getDatabaseConnectionParams(), new Configuration());

        $sourcePimRecords = $sourcePimConnection->fetchAll('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info');
        $destinationPimRecords = $destinationPimConnection->fetchAll('SELECT * FROM akeneo_pim.akeneo_file_storage_file_info');

        $this->assertEquals($sourcePimRecords, $destinationPimRecords);
    }

    public function tearDown()
    {
        parent::tearDown();

        $shudownPim17Database = new Process(sprintf('docker stop %s', $this->pim17ContainerName));
        $shudownPim20Database = new Process(sprintf('docker stop %s', $this->pim20ContainerName));

        $shudownPim17Database->run();
        $shudownPim20Database->run();

        $removePim17Database = new Process(sprintf('docker rm --volumes %s', $this->pim17ContainerName));
        $removePim20Database = new Process(sprintf('docker rm --volumes %s', $this->pim20ContainerName));

        $removePim17Database->run();
        $removePim20Database->run();
    }

    private function runCommand(string $command): Process
    {
        $process = new Process($command);

        $process->setTimeout(2 * 3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $authorizedExitCodes = [
                129, // Hangup
                130, // Interrupt
            ];
            if (!in_array($e->getProcess()->getExitCode(), $authorizedExitCodes)) {
                throw new UnsuccessfulCommandException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $process;
    }
}
