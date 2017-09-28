<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
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

    /** @var ChainedConsole */
    private $chainedConsole;

    /** @var FileSystemHelper */
    private $fileSystemHelper;

    public function __construct(
        FileSystemHelper $fileSystemHelper,
        FileFetcherRegistry $fileFetcherRegistry,
        ChainedConsole $chainedConsole
    ) {
        $this->chainedConsole = $chainedConsole;
        $this->fileFetcherRegistry = $fileFetcherRegistry;
        $this->fileSystemHelper = $fileSystemHelper;
    }

    public function fetchTableName(Pim $pim, string $entityNamespace): string
    {
        $mappingFilePath = DIRECTORY_SEPARATOR.'tmp';

        $this->chainedConsole->execute(
            new SymfonyCommand(
                sprintf(
                    'doctrine:mapping:convert --force --filter="%s" %s %s',
                    $entityNamespace,
                    'yml',
                    $mappingFilePath
                )
            ), $pim
        );

        $generatedFilePath = sprintf(
            '%s%s%s.orm.yml',
            $mappingFilePath,
            DIRECTORY_SEPARATOR,
            str_replace('\\', '.', $entityNamespace)
        );

        $localConfigPath = $this->fileFetcherRegistry->fetch($pim->getConnection(), $generatedFilePath, true);

        return $this->fileSystemHelper->getYamlContent($localConfigPath)[$entityNamespace]['table'];
    }
}
