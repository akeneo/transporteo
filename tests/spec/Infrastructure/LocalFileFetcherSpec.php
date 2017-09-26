<?php

namespace spec\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Spec for LocalFileFetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalFileFetcherSpec extends ObjectBehavior
{
    public function let(FileSystemHelper $fileSystemHelper)
    {
        $this->beConstructedWith($fileSystemHelper);

    }
    function it_is_initializable()
    {
        $this->shouldHaveType(LocalFileFetcher::class);
    }

    function it_throws_an_exception_if_the_file_does_not_exist(PimConnection $connection, $fileSystemHelper)
    {
        $path = '/home/plop/composer.json';

        $fileSystemHelper->fileExists($path)->willReturn(false);

        $this->shouldThrow(
            new FileNotFoundException("The file {$path} does not exist")
        )->during('fetch', [$connection, $path, true]);
    }

    function it_return_the_local_path(PimConnection $connection, $fileSystemHelper)
    {
        $path = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();

        $varDir = sprintf(
            '%s%s..%s..%svar',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $varDir = str_replace(
            sprintf('tests%sspec%sInfrastructure', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
            sprintf('src%sInfrastructure', DIRECTORY_SEPARATOR),
            $varDir
        );

        // REAL /home/anael/Developer/Akeneo/migration-tool/src/Infrastructure/../../var/composer.json
        // EXPECTED /home/anael/Developer/Akeneo/migration-tool/src/Infractructure/../../var/composer.json
        $localPath = sprintf('%s%s%s', $varDir, DIRECTORY_SEPARATOR, 'composer.json');

        $fileSystemHelper->fileExists($path)->willReturn(true);
        $fileSystemHelper->copyFile($path, $localPath, true)->shouldBeCalled();
        $fileSystemHelper->getRealPath($localPath)->willReturn('a-real-path');

        $this->fetch($connection, $path, true)->shouldReturn('a-real-path');
    }

    public function it_fetches_the_media_files(PimConnection $connection, $fileSystemHelper)
    {
        $fileSystemHelper->copyDirectory('/source/path', '/destination/path')->shouldBeCalled();

        $this->fetchMediaFiles($connection, '/source/path', '/destination/path');
    }
    
    function letGo()
    {
        $fs = new Filesystem();

        $fs->remove(ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath());
    }
}
