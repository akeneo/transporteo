<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Archive;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\GitDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\LocalArchiveDestinationPimDownloader;
use PHPUnit\Framework\TestCase;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Git downloader integration test.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloaderIntegration extends TestCase
{
    public function testItDownloadAPimProperlyViaArchive()
    {
        $resourcesRoot = ResourcesFileLocator::getStepFolder('step_four_download_destination_pim');

        $downloader = new LocalArchiveDestinationPimDownloader();

        $sourcePim = new SourcePim(
            'database_host',
            3306,
            'database_name',
            'database_user',
            'database_password',
            null,
            null,
            false,
            null,
            false,
            '/a-path'
        );

        $downloader->download(
            new Archive(sprintf(
                '%s%spim_community_standard_2_0.tar.gz',
                $resourcesRoot,
                DIRECTORY_SEPARATOR
                ))
            , $sourcePim,
            'test-project'
        );

        $destinationProjectPath = sprintf(
            '%s%stest-project',
            ResourcesFileLocator::getVarPath(),
            DIRECTORY_SEPARATOR
        );

        $this->assertFileExists($destinationProjectPath);
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();

        $destinationProjectPath = sprintf(
            '%s%stest-project',
            ResourcesFileLocator::getVarPath(),
            DIRECTORY_SEPARATOR
        );


        $fs->remove($destinationProjectPath);
    }
}
