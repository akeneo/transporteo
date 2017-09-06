<?php
declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Integration test about local file fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class LocalFileFetcherIntegration extends TestCase
{
    public function testItCopyTheFileCorrectly()
    {
        $localFileFetcher = new LocalFileFetcher(new FileSystemHelper());

        $localPath = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $path = $localFileFetcher->fetch(new Localhost(), $localPath, true);

        $this->assertFileExists($finalPath);
        $this->assertEquals($path, realpath($finalPath));
        $this->assertFileEquals($localPath, $finalPath);
    }

    public function testItThrowsAnExceptionIfFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $localFileFetcher = new LocalFileFetcher(new FileSystemHelper());

        $localPath = __DIR__ . '/anonexistingfile.json';

        $localFileFetcher->fetch(new Localhost(), $localPath, true);
    }

    public static function tearDownAfterClass()
    {
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $fs = new Filesystem();

        $fs->remove($finalPath);
    }
}
