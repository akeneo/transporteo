<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DockerDestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Infrastructure\Command\DockerComposeCommandLauncher;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Integration test to see if docker is really running.
 *
 * @group docker-compose
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerDestinationPimSystemRequirementsInstallerIntegration extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $fs = new Filesystem();

        $pimCommunityStandardPath = sprintf(
            '%s%spim_community_standard_2_0.tar.gz',
            ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
            DIRECTORY_SEPARATOR
        );

        $destinationPimPath = sprintf(
            '%s%spim_community_standard',
            ResourcesFileLocator::getVarPath(),
            DIRECTORY_SEPARATOR
        );

        $archive = new \PharData($pimCommunityStandardPath);

        $archive->extractTo($destinationPimPath, null, true);

        $parametersYmlPath = sprintf(
            '%s%sempty_pim_community_standard_2_0%sapp%sconfig%sparameters.yml',
            ResourcesFileLocator::getStepFolder('step_four_download_destination_pim'),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $destinationParametersYmlPath = sprintf(
            '%s%sapp%sconfig%sparameters.yml',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $fs->copy($parametersYmlPath, $destinationParametersYmlPath);
    }

    public function testDockerIsRunning()
    {
        $destinationPimPath = sprintf(
            '%s%spim_community_standard',
            ResourcesFileLocator::getVarPath(),
            DIRECTORY_SEPARATOR
        );

       $destinationPim = new DestinationPim(
           'mysql',
           3306,
           'akeneo_pim',
           'akeneo_pim',
           'akeneo_pim',
           false,
           null,
           'akeneo_pim',
           '\'elasticsearch: 9200\'',
           $destinationPimPath
       );

        $dockerDestinationInstaller = new DockerDestinationPimSystemRequirementsInstaller(new DockerComposeDestinationPimCommandLauncher('fpm'));
        $dockerDestinationInstaller->install($destinationPim);
        $this->addToAssertionCount(1);
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();

        $destinationPimPath = sprintf(
            '%s%spim_community_standard',
            ResourcesFileLocator::getVarPath(),
            DIRECTORY_SEPARATOR
        );

        $process = new Process('docker-compose down', $destinationPimPath);
        $process->run();

        $fs->remove($destinationPimPath);
    }
}
