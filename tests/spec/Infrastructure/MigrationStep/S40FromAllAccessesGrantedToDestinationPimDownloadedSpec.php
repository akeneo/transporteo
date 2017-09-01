<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\GitDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownloaderFactory;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S40FromAllAccessesGrantedToDestinationPimDownloaded;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * FromReadyToSourcePimConfiguredSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S40FromAllAccessesGrantedToDestinationPimDownloadedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        DestinationPimDownloaderFactory $destinationPimDownloaderFactory,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $destinationPimDownloaderFactory);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S40FromAllAccessesGrantedToDestinationPimDownloaded::class);
    }

    public function it_asks_the_pim_location(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        $printerAndAsker,
        $translator
    ) {
        $transPrefix = 'from_all_accesses_granted_to_destination_pim_downloaded.on_ask_destination_pim_location.';
        $translator->trans($transPrefix.'question')->willReturn('How do you want to install the destination PIM? ');
        $translator->trans($transPrefix.'docker_install')->willReturn('Using docker-compose');
        $translator->trans($transPrefix.'archive_install')->willReturn('I have a tar.gz archive, install it with docker');
        $translator->trans($transPrefix.'local_install')->willReturn('I have already installed a PIM 2.0');

        $event->getSubject()->willReturn($stateMachine);
        $printerAndAsker->askChoiceQuestion('How do you want to install the destination PIM? ', [
            'Using docker-compose',
            'I have a tar.gz archive, install it with docker',
            'I have already installed a PIM 2.0'
        ])->willReturn('Using docker-compose');

        $stateMachine->setDestinationPimLocation(0)->shouldBeCalled();
        $stateMachine->setUseDocker(true)->shouldBeCalled();

        $this->onAskDestinationPimLocation($event);
    }

    public function it_asks_to_download_the_pim(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        DestinationPimDownloaderFactory $destinationPimDownloaderFactory,
        GitDestinationPimDownloader $destinationPimDownloader,
        SourcePim $sourcePim
    ) {
        $event->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getProjectName()->willReturn('a-super-project');
        $stateMachine->getDestinationPathPimLocation()->willReturn('/home/akeneo/pim');
        $stateMachine->getDestinationPimLocation()->willReturn(0);

        $destinationPimDownloaderFactory->createGitDestinationPimDownloader()->willReturn($destinationPimDownloader);

        $destinationPimDownloader->download($sourcePim, 'a-super-project')->willReturn('/home/pim');

        $stateMachine->setCurrentDestinationPimLocation('/home/pim')->shouldBeCalled();

        $this->onDownloadingTransition($event);

    }
}
