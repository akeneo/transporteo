<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\GitDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\InstalledDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\LocalArchiveDestinationPimDownloader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Git downloader integration test.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloaderIntegration extends TestCase
{
    public function downloaderProvider()
    {
        $resourcesRoot = '/home/docker/migration/tests/resources/step_four_download_destination_pim/';

        return  [
           [new GitDestinationPimDownloader()],
            [new LocalArchiveDestinationPimDownloader($resourcesRoot . 'pim_community_standard_2_0.tar.gz')],
        ];
    }

    /**
     * @group plop
     * @dataProvider downloaderProvider
     */
    public function testItDownloadAPimProperly(DestinationPimDownloader $downloader)
    {
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
            false
        );

        $downloader->download($sourcePim, 'test-project');

        $this->assertFileExists('/home/docker/migration/var/test-project');
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Filesystem();

        $fs->remove('/home/docker/migration/var/test-project');
    }
}
