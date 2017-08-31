<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystem;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;

/**
 * Activate the Migration Bundle.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationBundleInstaller
{
    /** @var FileSystem */
    private $fileSystem;

    public function __construct(FileSystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function install(AbstractPim $pim): void
    {
        $this->copySources($pim);
        $this->setupKernel($pim);
    }

    private function setupKernel(AbstractPim $pim): void
    {
        $appKernelPath = sprintf(
            '%s%sapp%sAppKernel.php',
            $pim->getPath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $appKernelEmptyLine = $this->fileSystem->getFileLine($appKernelPath, 22);

        $indentation = '            ';
        $lineAfterClone = $indentation.'// your app bundles should be registered here'.PHP_EOL;
        $lineToAdd = $indentation."new Akeneo\Bundle\MigrationBundle\AkeneoMigrationBundle(),".PHP_EOL;

        if ($lineAfterClone !== $appKernelEmptyLine && $lineToAdd !== $appKernelEmptyLine) {
            throw new \InvalidArgumentException('The AppKernel is not a raw kernel');
        }

        $this->fileSystem->updateLineInFile($appKernelPath, 22, $lineToAdd);
    }

    private function copySources(AbstractPim $pim): void
    {
        $from = $this->fileSystem->getRealPath(
            sprintf(
                '%s%sconfig%sakeneo_migration_bundle.tar.gz',
                __DIR__,
                DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR
            )
        );

        $to = sprintf(
            '%s%sAkeneo',
            sprintf('%s%ssrc', $pim->getPath(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR
        );

        $this->fileSystem->extractArchive($from, $to);
    }
}
