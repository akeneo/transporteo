<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;

/**
 * Get an entity table name using doctrine mapping info through a command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EntityTableNameFetcher
{
    /** @var FileFetcherRegistry */
    private $fileFetcherRegistry;

    /** @var ConsoleHelper */
    private $consoleHelper;

    /** @var FileSystemHelper */
    private $fileSystemHelper;

    public function __construct(
        FileSystemHelper $fileSystemHelper,
        FileFetcherRegistry $fileFetcherRegistry,
        ConsoleHelper $consoleHelper
    ) {
        $this->consoleHelper = $consoleHelper;
        $this->fileFetcherRegistry = $fileFetcherRegistry;
        $this->fileSystemHelper = $fileSystemHelper;
    }

    public function fetchTableName(Pim $pim, string $entityNamespace): string
    {
        $mappingFilePath = $pim->absolutePath();

        $this->consoleHelper->execute(
            $pim,
            new SymfonyCommand(
                sprintf(
                    'doctrine:mapping:convert --force --filter="%s" %s %s',
                    $entityNamespace,
                    'yml',
                    $mappingFilePath
                )
            )
        );

        $generatedFilePath = sprintf(
            '%s%s%s.orm.yml',
            $mappingFilePath,
            DIRECTORY_SEPARATOR,
            str_replace('\\', '.', $entityNamespace)
        );

        $localConfigPath = $this->fileFetcherRegistry->fetch($pim, $generatedFilePath, true);

        return $this->fileSystemHelper->getYamlContent($localConfigPath)[$entityNamespace]['table'];
    }
}
