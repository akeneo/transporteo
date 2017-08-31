<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystem;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Symfony\Component\Yaml\Yaml;

/**
 * Configures a reference data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataConfigurator
{
    /** @var FileSystem */
    private $fileSystem;

    public function __construct(FileSystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function configure(array $referenceDataConfig, string $tableName, AbstractPim $pim): string
    {
        $classPath = $referenceDataConfig['class'];

        $sampleOrmPath = sprintf(
            '%s%sconfig%sSample.orm.yml',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $sampleReferenceData = Yaml::parse($this->fileSystem->getFileContent($sampleOrmPath));

        $className = substr($classPath, strrpos($classPath, '\\') + 1);

        $newClassPath = 'Akeneo\\Bundle\\MigrationBundle\\Entity\\'.$className;
        $sampleReferenceData['THE_CLASS_NAME']['table'] = $tableName;
        $sampleReferenceData[$newClassPath] = $sampleReferenceData['THE_CLASS_NAME'];
        unset($sampleReferenceData['THE_CLASS_NAME']);

        $destinationEntityDefinitionPath = sprintf(
            '%s%ssrc%sAkeneo%sBundle%sMigrationBundle%sResources%sconfig%sdoctrine%s%s.orm.yml',
            $pim->getPath(),
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

        $entityDefinitionYaml = Yaml::dump($sampleReferenceData);

        $this->fileSystem->getFileSystem()->dumpFile($destinationEntityDefinitionPath, $entityDefinitionYaml);

        $destinationEntityPath = sprintf(
            '%s%ssrc%s%s.php',
            $pim->getPath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            str_replace('\\', DIRECTORY_SEPARATOR, $newClassPath)
        );

        $sampleEntityPath = sprintf('%s%sconfig%sEntity.php', __DIR__, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $this->fileSystem->getFileSystem()->copy($sampleEntityPath, $destinationEntityPath);

        $classNameLine = $this->fileSystem->getFileLine($destinationEntityPath, 14);

        $this->fileSystem->updateLineInFile($destinationEntityPath, 14, str_replace('THE_CLASS_NAME', $className, $classNameLine));

        $configFilePath = sprintf(
            '%s%sapp%sconfig%sconfig.yml',
            $pim->getPath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $configFile = Yaml::parse($this->fileSystem->getFileContent($configFilePath));

        $referenceDataConfig['class'] = $newClassPath;

        $configFile['pim_reference_data'][] = $referenceDataConfig;

        $configFileUpdated = Yaml::dump($configFile, 4);

        $this->fileSystem->getFileSystem()->dumpFile($configFilePath, $configFileUpdated);

        return $newClassPath;
    }
}
