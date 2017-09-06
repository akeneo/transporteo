<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\FileSystemHelper;

/**
 * Generate a default parameters yaml file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ParametersYmlGenerator
{
    /** @var FileSystemHelper */
    private $fileSystemHelper;

    public function __construct(FileSystemHelper $fileSystemHelper)
    {
        $this->fileSystemHelper = $fileSystemHelper;
    }

    public function preconfigure(string $pimPath): void
    {
        $configPath = sprintf(
            '%s%sapp%sconfig',
            $pimPath,
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

        $this->fileSystemHelper->copyFile($parametersYamlDistPath, $parametersYamlPath, true);

        $parameters = $this->fileSystemHelper->getYamlContent($parametersYamlPath);

        $parameters['parameters']['database_host'] = 'mysql';
        $parameters['parameters']['database_port'] = 3306;
        $parameters['parameters']['index_name'] = 'akeneo_pim';
        $parameters['parameters']['index_hosts'] = "'elasticsearch: 9200'";

        $this->fileSystemHelper->dumpYamlInFile($parametersYamlPath, $parameters);
    }
}
