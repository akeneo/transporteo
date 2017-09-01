<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Generate a default parameters yaml file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ParametersYmlGenerator
{
    /** @var string */
    private $destinationPimPath;

    public function __construct(string $destinationPimPath)
    {
        $this->destinationPimPath = $destinationPimPath;
    }

    public function preconfigure(): void
    {
        $configPath = sprintf(
            '%s%sapp%sconfig',
            $this->destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $parametersYamlDistPath = sprintf(
            '%s%sparameters.yml.dist',
            $configPath,
            DIRECTORY_SEPARATOR
        );

        $parametersYamlPath = sprintf(
            '%s%sparameters.yml',
            $configPath,
            DIRECTORY_SEPARATOR
        );

        $fs = new Filesystem();

        $fs->copy($parametersYamlDistPath, $parametersYamlPath);

        $parameters = Yaml::parse(file_get_contents($parametersYamlPath));

        $parameters['parameters']['database_host'] = 'mysql';
        $parameters['parameters']['database_port'] = 3306;
        $parameters['parameters']['index_name'] = 'akeneo_pim';
        $parameters['parameters']['index_hosts'] = "'elasticsearch: 9200'";

        $parametersYaml = Yaml::dump($parameters);
        file_put_contents($parametersYamlPath, $parametersYaml);
    }
}
