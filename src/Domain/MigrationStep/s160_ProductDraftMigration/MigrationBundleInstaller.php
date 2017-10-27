<?php

namespace Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration;

use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationBundleInstaller
{
    const SOURCE = 'Source';
    const DESTINATION = 'Destination';

    /** @var FileSystemHelper */
    private $fileSystem;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(FileSystemHelper $fileSystem, LoggerInterface $logger)
    {
        $this->fileSystem = $fileSystem;
        $this->logger = $logger;
    }

    public function install(Pim $pim): void
    {
        $this->copySources($pim);
        $this->setupKernel($pim);
    }

    private function setupKernel(Pim $pim): void
    {
        $this->logger->debug('MigrationBundleInstaller: Start setup kernel');

        $appKernelPath = sprintf('%s/app/AppKernel.php', $pim->absolutePath());

        $l28Content = '        $bundles = [' . PHP_EOL;
        $l29Content = '            new Akeneo\Bundle\MigrationBundle\AkeneoMigrationBundle(),' . PHP_EOL;

        $appKernelLine28 = $this->fileSystem->getFileLine($appKernelPath, 28);
        $appKernelLine29 = $this->fileSystem->getFileLine($appKernelPath, 29);

        if ($l28Content !== $appKernelLine28) {
            throw new \InvalidArgumentException('The AppKernel of your source PIM is not a raw kernel');
        }

        if ($l29Content !== $appKernelLine29) {
            $lineToAdd = $l28Content . "            new Akeneo\Bundle\MigrationBundle\AkeneoMigrationBundle()," . PHP_EOL;
            $this->fileSystem->updateLineInFile($appKernelPath, 28, $lineToAdd);
        }

        $this->logger->debug('MigrationBundleInstaller: Kernel setup finish');
    }

    private function copySources(Pim $pim): void
    {
        $from = sprintf('%s/config/Akeneo', __DIR__);
        $to = sprintf('%s/src/Akeneo', $pim->absolutePath());

        $this->fileSystem->copyDirectory($from, $to);
    }
}
