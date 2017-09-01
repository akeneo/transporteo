<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\ParametersYmlGenerator;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Integration test for ParametersYml generator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ParametersYmlGeneratorIntegration extends TestCase
{
    /** @var string */
    private $destinationPimPath;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->destinationPimPath = sprintf(
            '%s%spim_community_standard',
            ResourcesFileLocator::getVarPath(),
            DIRECTORY_SEPARATOR
        );
    }

    public function setUp()
    {
        parent::setUp();

        $resourcePath = ResourcesFileLocator::getStepFolder('step_four_download_destination_pim');

        $archivePimPath = sprintf(
            '%s%spim_community_standard_2_0.tar.gz',
            $resourcePath,
            DIRECTORY_SEPARATOR
        );

        $archive = new \PharData($archivePimPath);

        $archive->extractTo($this->destinationPimPath, null, true);
    }

    public function testItCopyTheParametersYamlDist()
    {
        $preConfigurator = new ParametersYmlGenerator($this->destinationPimPath);
        $preConfigurator->preconfigure();

        $parametersYamlPath = sprintf(
            '%s%sapp%sconfig%sparameters.yml',
            $this->destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $this->assertFileExists($parametersYamlPath);

        $parametersActual = Yaml::parse(file_get_contents($parametersYamlPath));

        $parametersExpected = [
            'parameters' => [
                'database_driver' => 'pdo_mysql',
                'database_host' => 'mysql',
                'database_port' => 3306,
                'database_name' => 'akeneo_pim',
                'database_user' => 'akeneo_pim',
                'database_password' => 'akeneo_pim',
                'locale' => 'en',
                'secret' => 'ThisTokenIsNotSoSecretChangeIt',
                'index_name' => 'akeneo_pim',
                'index_hosts' => '\'elasticsearch: 9200\'',
            ]
        ];

        $this->assertEquals($parametersExpected, $parametersActual);
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();

        $fs->remove($this->destinationPimPath);
    }
}
