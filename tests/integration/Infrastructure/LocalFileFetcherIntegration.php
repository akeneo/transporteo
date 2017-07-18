<?php
declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

final class LocalFileFetcherIntegration extends TestCase
{
    public function testItCopyTheFileCorrectly()
    {
        $localFileFetcher = new LocalFileFetcher();

        $localPath = ResourcesFileLocator::getAbsoluteComposerJsonLocalPath();
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $path = $localFileFetcher->fetch($localPath);

        $this->assertFileExists($finalPath);
        $this->assertEquals($path, realpath($finalPath));
        $this->assertFileEquals($localPath, $finalPath);
    }

    public function testItThrowsAnExceptionIfFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $localFileFetcher = new LocalFileFetcher();

        $localPath = __DIR__ . '/anonexistingfile.json';

        $localFileFetcher->fetch($localPath);
    }

    public static function tearDownAfterClass()
    {
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $fs = new Filesystem();

        $fs->remove($finalPath);
    }

}
