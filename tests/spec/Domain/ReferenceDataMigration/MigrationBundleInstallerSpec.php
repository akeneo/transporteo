<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystem;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Domain\ReferenceDataMigration\MigrationBundleInstaller;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;

/**
 * Spec for Migration Bundle Installer.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationBundleInstallerSpec extends ObjectBehavior
{
    public function let(FileSystem $fileSystem)
    {
        $this->beConstructedWith($fileSystem);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MigrationBundleInstaller::class);
    }

    public function it_installs(
        AbstractPim $pim,
        $fileSystem
    ) {
        $destinationPimPath = '/a-path';
        $pim->getPath()->willReturn($destinationPimPath);

        $referenceDataMigrationConfigDir = sprintf(
            '%s%sDomain%sReferenceDataMigration%sconfig',
            ResourcesFileLocator::getSrcDir(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $archivePath = sprintf(
            '%s%sakeneo_migration_bundle.tar.gz',
            $referenceDataMigrationConfigDir,
            DIRECTORY_SEPARATOR
        );
        $fileSystem->getRealPath($archivePath)->willReturn($archivePath);
        $fileSystem->extractArchive($archivePath, '/a-path/src/Akeneo')->shouldBeCalled();


        $appKernelPath = sprintf(
            '%s%sapp%sAppKernel.php',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $fileSystem->getFileLine($appKernelPath, 22)->willReturn(
            '            // your app bundles should be registered here'.PHP_EOL
        );

        $lineToAdd = "            new Akeneo\Bundle\MigrationBundle\AkeneoMigrationBundle(),".PHP_EOL;

        $fileSystem->updateLineInFile($appKernelPath, 22, $lineToAdd)->shouldBeCalled();

        $this->install($pim);
    }

    public function it_throws_an_exception(
        AbstractPim $pim,
        $fileSystem
    ) {
        $destinationPimPath = '/a-path';
        $pim->getPath()->willReturn($destinationPimPath);

        $referenceDataMigrationConfigDir = sprintf(
            '%s%sDomain%sReferenceDataMigration%sconfig',
            ResourcesFileLocator::getSrcDir(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $archivePath = sprintf(
            '%s%sakeneo_migration_bundle.tar.gz',
            $referenceDataMigrationConfigDir,
            DIRECTORY_SEPARATOR
        );
        $fileSystem->getRealPath($archivePath)->willReturn($archivePath);
        $fileSystem->extractArchive($archivePath, '/a-path/src/Akeneo')->shouldBeCalled();


        $appKernelPath = sprintf(
            '%s%sapp%sAppKernel.php',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $fileSystem->getFileLine($appKernelPath, 22)->willReturn(
            '          A weird line'.PHP_EOL
        );

        $this->shouldThrow(new \InvalidArgumentException('The AppKernel is not a raw kernel'))->during('install', [$pim]);
    }
}
