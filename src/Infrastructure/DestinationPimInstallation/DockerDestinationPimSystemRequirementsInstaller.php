<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsNotBootable;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Infrastructure\Command\DestinationPimCommandLauncher;
use Ds\Set;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Install Pim System Requirements through docker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerDestinationPimSystemRequirementsInstaller implements DestinationPimSystemRequirementsInstaller
{
    /** @var DestinationPimCommandLauncher */
    private $dockerDestinationPimCommandLauncher;

    /** @var DestinationPimCommandLauncher */
    private $basicDestinationPimCommandLauncher;

    public function __construct(
        DestinationPimCommandLauncher $dockerDestinationPimCommandLauncher,
        DestinationPimCommandLauncher $basicDestinationPimCommandLauncher
    ) {
        $this->dockerDestinationPimCommandLauncher = $dockerDestinationPimCommandLauncher;
        $this->basicDestinationPimCommandLauncher = $basicDestinationPimCommandLauncher;
    }

    public function install(DestinationPim $destinationPim): void
    {
        $dockerComposeDistFilePath = sprintf(
            '%s%sdocker-compose.yml.dist',
            $destinationPim->getPath(),
            DIRECTORY_SEPARATOR
        );

        $dockerComposeDestinationPath = sprintf(
            '%s%sdocker-compose.yml',
            $destinationPim->getPath(),
            DIRECTORY_SEPARATOR
        );

        $dockerComposeConfig = Yaml::dump($this->setupDockerComposePorts($dockerComposeDistFilePath));

        $fs = new Filesystem();
        $fs->dumpFile($dockerComposeDestinationPath, $dockerComposeConfig);

        $this->basicDestinationPimCommandLauncher->runCommand(new DockerComposeUpDaemonCommand(), $destinationPim);

        if (!$this->dockerComposeInfrastructureIsUp($destinationPim)) {
            throw new DestinationPimSystemRequirementsNotBootable(
                'Docker cannot boot the install system, please check `docker-compose ps` in '.$destinationPim->getPath()
            );
        }

        $this->dockerDestinationPimCommandLauncher->runCommand(new ComposerUpdateCommand(), $destinationPim);
        $this->dockerDestinationPimCommandLauncher->runCommand(new PrepareRequiredDirectoriesCommand(), $destinationPim);
        $this->dockerDestinationPimCommandLauncher->runCommand(new DoctrineDropDatabaseCommand(), $destinationPim);
        $this->dockerDestinationPimCommandLauncher->runCommand(new DoctrineCreateDatabaseCommand(), $destinationPim);
        $this->dockerDestinationPimCommandLauncher->runCommand(new DoctrineCreateSchemaCommand(), $destinationPim);
        $this->dockerDestinationPimCommandLauncher->runCommand(new DoctrineUpdateSchemaCommand(), $destinationPim);
    }

    protected function setupDockerComposePorts(string $dockerComposePath): array
    {
        $dockerConfig = Yaml::parse(file_get_contents($dockerComposePath));

        $dockerConfig['services']['httpd']['ports'][0] = '9991:80';
        $dockerConfig['services']['mysql']['ports'][0] = '9992:3306';
        $dockerConfig['services']['elasticsearch']['ports'][0] = '9993:9200';

        return $dockerConfig;
    }

    protected function dockerComposeInfrastructureIsUp(DestinationPim $destinationPim): bool
    {
        $folderName = basename($destinationPim->getPath());
        $containerPrefix = str_replace(['-', '_'], '', $folderName);

        $services = [
            $containerPrefix.'_httpd_1',
            $containerPrefix.'_mysql_1',
            $containerPrefix.'_elasticsearch_1',
            $containerPrefix.'_fpm_1',
        ];

        $getContainerRunningProcess = $this
            ->basicDestinationPimCommandLauncher
            ->runCommand(new DockerGetContainersRunningCommand(), $destinationPim);

        $servicesNames = new Set(explode(PHP_EOL, $getContainerRunningProcess->getOutput()));

        return $servicesNames->filter(function (string $service) {
            return !empty(trim($service));
        })->contains(...$services);
    }
}
