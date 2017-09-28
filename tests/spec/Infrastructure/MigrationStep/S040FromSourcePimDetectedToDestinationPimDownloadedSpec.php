<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloaderHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Local;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S040FromSourcePimDetectedToDestinationPimDownloaded;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * FromReadyToSourcePimConfiguredSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S040FromSourcePimDetectedToDestinationPimDownloadedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        LoggerInterface $logger,
        DestinationPimDownloaderHelper $destinationPimDownloaderHelper,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $logger, $destinationPimDownloaderHelper);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S040FromSourcePimDetectedToDestinationPimDownloaded::class);
    }

    public function it_asks_the_pim_location(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        $printerAndAsker,
        $translator
    ) {
        $transPrefix = 'from_all_accesses_granted_to_destination_pim_downloaded.on_ask_destination_pim_location.';

        $localPimPathQuestion = 'What is the absolute path of your local destination PIM? ';
        $translator->trans($transPrefix.'local_pim_path_question')->willReturn($localPimPathQuestion);

        $event->getSubject()->willReturn($stateMachine);

        $pimPath = '/an-absolute-pim-path';
        $printerAndAsker->askSimpleQuestion($localPimPathQuestion, '', Argument::any())->willReturn($pimPath);

        $stateMachine->setDownloadMethod(new Local($pimPath))->shouldBeCalled();
        $stateMachine->setDestinationPimConnection(new Localhost())->shouldBeCalled();

        $this->onAskDestinationPimLocation($event);
    }

    public function it_asks_to_download_the_pim_with_docker(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        DownloadMethod $downloadMethod,
        $destinationPimDownloaderHelper
    ) {
        $event->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getProjectName()->willReturn('a-super-project');
        $stateMachine->getDownloadMethod()->willReturn($downloadMethod);

        $destinationPimDownloaderHelper->download($downloadMethod, $sourcePim, 'a-super-project')->willReturn('/home/pim');

        $stateMachine->setCurrentDestinationPimLocation('/home/pim')->shouldBeCalled();

        $this->onDownloadingTransition($event);

    }
}
