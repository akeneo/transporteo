<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\EntityTableNameFetcher;
use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Symfony\Component\Yaml\Yaml;

/**
 * Get an entity table name using doctrine mapping info through a command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandEntityTableNameFetcher implements EntityTableNameFetcher
{
    /** @var FileFetcher */
    private $sourcePimFileFetcher;

    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(FileFetcher $sourcePimFileFetcher, CommandLauncher $commandLauncher)
    {
        $this->sourcePimFileFetcher = $sourcePimFileFetcher;
        $this->commandLauncher = $commandLauncher;
    }

    public function fetchTableName(AbstractPim $pim, string $entityNamespace): string
    {
        $mappingFilePath = $pim->getPath();

        $this->commandLauncher->runCommand(
            new DoctrineMappingConvertCommand($entityNamespace, 'yml', $mappingFilePath),
            $pim->getPath(), false
        );

        $generatedFilePath = sprintf(
            '%s%s%s.orm.yml',
            $mappingFilePath,
            DIRECTORY_SEPARATOR,
            str_replace('\\', '.', $entityNamespace)
        );

        $localConfigPath = $this->sourcePimFileFetcher->fetch($generatedFilePath);

        return Yaml::parse(file_get_contents($localConfigPath))[$entityNamespace]['table'];
    }
}
