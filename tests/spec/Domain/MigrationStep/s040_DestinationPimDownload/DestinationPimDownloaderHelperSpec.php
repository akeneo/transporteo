<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutorRegistry;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloaderHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for DatabaseQueryExecutor.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimDownloaderHelperSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DestinationPimDownloaderHelper::class);
    }

    public function it_gets_a_supportable_query_executor(
        DestinationPimDownloader $destinationPimDownloader1,
        DestinationPimDownloader $destinationPimDownloader2,
        DownloadMethod $downloadMethod,
        SourcePim $pim
    ) {
        $this->addDestinationPimDownloader($destinationPimDownloader1);
        $this->addDestinationPimDownloader($destinationPimDownloader2);

        $destinationPimDownloader1->supports($downloadMethod)->willReturn(false);
        $destinationPimDownloader2->supports($downloadMethod)->willReturn(true);
        $destinationPimDownloader2->download($downloadMethod, $pim, 'a-project-name')->willReturn('something');

        $this->download($downloadMethod, $pim, 'a-project-name')->shouldReturn('something');
    }

    public function it_throws_an_exception_if_there_is_no_console_supporting_the_connection(
        DestinationPimDownloader $destinationPimDownloader1,
        DestinationPimDownloader $destinationPimDownloader2,
        DownloadMethod $downloadMethod,
        SourcePim $pim
    ) {
        $this->addDestinationPimDownloader($destinationPimDownloader1);
        $this->addDestinationPimDownloader($destinationPimDownloader2);

        $destinationPimDownloader1->supports($downloadMethod)->willReturn(false);
        $destinationPimDownloader2->supports($downloadMethod)->willReturn(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('The download method is not supported by any DestinationPimDownloader'))
            ->during('download', [$downloadMethod, $pim, 'a-project-name']);
    }
}
