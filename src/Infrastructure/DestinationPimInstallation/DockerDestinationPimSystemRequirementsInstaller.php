<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Command\RawCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsNotBootable;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;
use Ds\Set;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Install Pim System Requirements through docker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerDestinationPimSystemRequirementsInstaller implements DestinationPimSystemRequirementsInstaller
{
    /** @var ConsoleHelper */
    private $consoleHelper;

    public function __construct(ConsoleHelper $consoleHelper)
    {
        $this->consoleHelper = $consoleHelper;
    }

    public function install(DestinationPim $destinationPim): void
    {
        $this->bootInfrastructure($destinationPim);
        $this->prepareDependencies($destinationPim);
        $this->prepareDatabase($destinationPim);
    }

    protected function bootInfrastructure(Pim $pim)
    {
        $dockerComposeDistFilePath = sprintf(
            '%s%sdocker-compose.yml.dist',
            $pim->absolutePath(),
            DIRECTORY_SEPARATOR
        );

        $dockerComposeDestinationPath = sprintf(
            '%s%sdocker-compose.yml',
            $pim->absolutePath(),
            DIRECTORY_SEPARATOR
        );

        $fs = new Filesystem();

        $fs->copy($dockerComposeDistFilePath, $dockerComposeDestinationPath);

        $launchDockerComposeDaemon = new Process('docker-compose up -d', $pim->absolutePath());

        $launchDockerComposeDaemon->run();

        if (!$this->dockerComposeInfrastructureIsUp($pim->absolutePath())) {
            throw new DestinationPimSystemRequirementsNotBootable(
                'Docker cannot boot the install system, please check `docker-compose ps` in '.$pim->absolutePath()
            );
        }
    }

    protected function prepareDependencies(Pim $pim)
    {
        $this->consoleHelper->execute($pim, new RawCommand('composer update'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('pim:installer:prepare-required-directories'));
    }

    protected function prepareDatabase(Pim $pim)
    {
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:database:drop --force'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:database:create'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:schema:create'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:schema:update --force'));
    }

    protected function dockerComposeInfrastructureIsUp(string $destinationPimPath): bool
    {
        $folderName = basename($destinationPimPath);
        $containerPrefix = str_replace(['-', '_'], '', $folderName);

        $services = [
            $containerPrefix.'_httpd_1',
            $containerPrefix.'_mysql_1',
            $containerPrefix.'_elasticsearch_1',
            $containerPrefix.'_fpm_1',
        ];

        $process = new Process('docker ps --format="{{.Names}}"', $destinationPimPath);
        $process->run();
        $output = $process->getOutput();

        $output = explode(PHP_EOL, $output);

        $servicesNames = new Set($output);

        return $servicesNames->filter(function (string $service) {
            return !empty(trim($service));
        })->contains(...$services);
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof DockerConnection;
    }
}
