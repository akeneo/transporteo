<?php

namespace spec\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileNotFoundException;
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
    function it_is_initializable()
    {
        $this->shouldHaveType(LocalFileFetcher::class);
    }

    function it_throws_an_exception_if_the_file_does_not_exist()
    {
        $path = '/home/plop/composer.json';

        $this->shouldThrow(
            new FileNotFoundException("The file {$path} does not exist")
        )->during('fetch', [$path]);
    }

    function it_return_the_local_path()
    {
        $path = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $this->fetch($path)->shouldReturn(realpath($finalPath));
    }

    function letGo()
    {
        $fs = new Filesystem();

        $fs->remove(ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath());
    }
}
