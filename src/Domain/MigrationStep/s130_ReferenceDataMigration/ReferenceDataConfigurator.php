<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Configures a reference data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataConfigurator
{
    /** @var FileSystemHelper */
    private $fileSystem;

    public function __construct(FileSystemHelper $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function configure(array $referenceDataConfig, string $tableName, Pim $pim): string
    {
        $classPath = $referenceDataConfig['class'];

        $sampleOrmPath = sprintf(
            '%s%sconfig%sSample.orm.yml',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $sampleReferenceData = $this->fileSystem->getYamlContent($sampleOrmPath);

        $className = substr($classPath, strrpos($classPath, '\\') + 1);

        $newClassPath = 'Akeneo\\Bundle\\MigrationBundle\\Entity\\'.$className;
        $sampleReferenceData['THE_CLASS_NAME']['table'] = $tableName;
        $sampleReferenceData[$newClassPath] = $sampleReferenceData['THE_CLASS_NAME'];
        unset($sampleReferenceData['THE_CLASS_NAME']);

        $destinationEntityDefinitionPath = sprintf(
            '%s%ssrc%sAkeneo%sBundle%sMigrationBundle%sResources%sconfig%sdoctrine%s%s.orm.yml',
            $pim->absolutePath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $className
        );

        $this->fileSystem->dumpYamlInFile($destinationEntityDefinitionPath, $sampleReferenceData);

        $destinationEntityPath = sprintf(
            '%s%ssrc%s%s.php',
            $pim->absolutePath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            str_replace('\\', DIRECTORY_SEPARATOR, $newClassPath)
        );

        $sampleEntityPath = sprintf('%s%sconfig%sEntity.php', __DIR__, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $this->fileSystem->copyFile($sampleEntityPath, $destinationEntityPath, true);

        $classNameLine = $this->fileSystem->getFileLine($destinationEntityPath, 15);

        $this->fileSystem->updateLineInFile($destinationEntityPath, 15, str_replace('THE_CLASS_NAME', $className, $classNameLine));

        $configFilePath = sprintf(
            '%s%sapp%sconfig%sconfig.yml',
            $pim->absolutePath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $configFile = $this->fileSystem->getYamlContent($configFilePath);

        $referenceDataConfig['class'] = $newClassPath;

        $configFile['pim_reference_data'][] = $referenceDataConfig;

        $this->fileSystem->dumpYamlInFile($configFilePath, $configFile);

        return $newClassPath;
    }
}
