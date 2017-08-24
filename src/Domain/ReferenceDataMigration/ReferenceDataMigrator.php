<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\Command\CommandLauncher;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Migrator for reference data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataMigrator implements DataMigrator
{
    /** @var CommandLauncher */
    private $sourcePimCommandLauncher;

    /** @var FileFetcher */
    private $sourcePimFileFetcher;

    /** @var FileFetcher */
    private $destinationPimFileFetcher;

    public function __construct(
        CommandLauncher $sourcePimCommandLauncher,
        FileFetcher $sourcePimFileFetcher,
        FileFetcher $destinationPimFileFetcher
    ) {
        $this->sourcePimCommandLauncher = $sourcePimCommandLauncher;
        $this->sourcePimFileFetcher = $sourcePimFileFetcher;
        $this->destinationPimFileFetcher = $destinationPimFileFetcher;
    }

    /**
     * @throws DataMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $bundleName = 'PimReferenceDataBundle';

        $commandResult = $this
            ->sourcePimCommandLauncher
            ->runCommand(new DebugConfigCommand($bundleName), $sourcePim->getPath(), false);

        $header = sprintf('# Current configuration for "%s"%s', $bundleName, PHP_EOL);

        $referenceDataConfig = Yaml::parse(str_replace($header, '', $commandResult->getOutput()));

        //COPY THE NEW BUNDLE LE BUNDLE
        $from = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'config'. DIRECTORY_SEPARATOR . 'Akeneo');
        $to = realpath($destinationPim->getPath() . DIRECTORY_SEPARATOR . 'src'). DIRECTORY_SEPARATOR . 'Akeneo';

        $this->copyDirectory($from, $to);

        //ACTIVER LE BUNDLE DANS LE KERNEL
        $appKernelPath = $this->destinationPimFileFetcher->fetch($destinationPim->getPath() .DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'AppKernel.php');

        echo "plop";
    }

    private function copyDirectory(string $from, string $to): void
    {
        $fs = new Filesystem();

        if (file_exists($to))
        {
            $fs->remove($to);
        }

        $fs->mkdir($to);

        $directoryIterator = new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item)
        {
            if ($item->isDir()) {
                $fs->mkdir($to . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                continue;
            }

            $fs->copy($item, $to . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}
