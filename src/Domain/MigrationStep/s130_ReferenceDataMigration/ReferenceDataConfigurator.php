<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Configures a reference data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataConfigurator
{
    public const ASSET_NAMESPACE = 'PimEnterprise\\Component\\ProductAsset\\Model\\Asset';

    /** @var FileSystemHelper */
    private $fileSystem;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(FileSystemHelper $fileSystem, LoggerInterface $logger)
    {
        $this->fileSystem = $fileSystem;
        $this->logger = $logger;
    }

    public function configure(string $referenceDataName, array $referenceDataConfig, string $tableName, Pim $pim): string
    {
        $this->logger->debug('ReferenceDataConfigurator: Start configuring');

        $classPath = $referenceDataConfig['class'];

        if (self::ASSET_NAMESPACE === $classPath && 'pimee_product_asset_asset' === $tableName) {
            $this->logger->debug('ReferenceDataConfigurator: Finish configuring');

            return $classPath;
        }

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

        $configFile['pim_reference_data'][$referenceDataName] = $referenceDataConfig;

        $this->fileSystem->dumpYamlInFile($configFilePath, $configFile);

        $this->logger->debug('ReferenceDataConfigurator: Finish configuring');

        return $newClassPath;
    }
}
