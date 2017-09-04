<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Activate the Migration Bundle.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationBundleInstaller
{
    /** @var FileSystemHelper */
    private $fileSystem;

    public function __construct(FileSystemHelper $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function install(Pim $pim): void
    {
        $this->copySources($pim);
        $this->setupKernel($pim);
    }

    private function setupKernel(Pim $pim): void
    {
        $appKernelPath = sprintf(
            '%s%sapp%sAppKernel.php',
            $pim->absolutePath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $appKernelEmptyLine = $this->fileSystem->getFileLine($appKernelPath, 23);

        $indentation = '            ';
        $lineAfterClone = $indentation.'// your app bundles should be registered here'.PHP_EOL;
        $lineToAdd = $indentation."new Akeneo\Bundle\MigrationBundle\AkeneoMigrationBundle(),".PHP_EOL;

        if ($lineAfterClone !== $appKernelEmptyLine && $lineToAdd !== $appKernelEmptyLine) {
            throw new \InvalidArgumentException('The AppKernel is not a raw kernel');
        }

        $this->fileSystem->updateLineInFile($appKernelPath, 23, $lineToAdd);
    }

    private function copySources(Pim $pim): void
    {
        $from = sprintf(sprintf(
            '%s%sconfig%sAkeneo',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        ));

        $to = sprintf('%s%ssrc%sAkeneo', $pim->absolutePath(), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $this->fileSystem->copyDirectory($from, $to);
    }
}
