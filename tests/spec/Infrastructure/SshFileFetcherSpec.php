<?php

namespace spec\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Infrastructure\SshFileFetcher;
use phpseclib\Net\SFTP;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;

/**
 * Spec for SshFileFetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright ${YEAR} Akeneo SAS (http://www.akeneo.com)
 */
class SshFileFetcherSpec extends ObjectBehavior
{
    public function let(SFTP $sftp)
    {
        $this->beConstructedWith($sftp);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SshFileFetcher::class);
    }

    function it_throws_an_exception_if_the_file_does_not_exist($sftp)
    {
        $path = '/home/plop/composer.json';
        $pathInfo = pathinfo($path);

        $sftp->nlist($pathInfo['dirname'])->willReturn(['afile.jyon']);

        $this->shouldThrow(
            new FileNotFoundException("The file {$path} does not exist")
        )->during('fetch', [$path]);
    }

    function it_return_the_local_path($sftp)
    {
        $path = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $pathInfo = pathinfo($path);
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $sftp->nlist($pathInfo['dirname'])->willReturn(['composer.json']);

        $sftp->get($path, $finalPath)->willReturn(true);

        $this->fetch($path)->shouldReturn($finalPath);
    }

    function it_throws_an_exception_if_there_is_problem_during_ssh_processing($sftp)
    {
        $path = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $pathInfo = pathinfo($path);
        $finalPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();

        $sftp->nlist($pathInfo['dirname'])->willReturn(['composer.json']);

        $sftp->get($path, $finalPath)->willReturn(false);

        $this->shouldThrow(new \RuntimeException("The file {$path} is not reachable"))->during('fetch', [$path]);
    }
}
