<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\GitDestinationPimDownloader;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownloaderFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromAllAccessesGrantedToDestinationPimDownloaded;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Workflow\Event\Event;

/**
 * FromReadyToSourcePimConfiguredSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromAllAccessesGrantedToDestinationPimDownloadedSpec extends ObjectBehavior
{
    public function let(
        DestinationPimDownloaderFactory $destinationPimDownloaderFactory,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($destinationPimDownloaderFactory);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FromAllAccessesGrantedToDestinationPimDownloaded::class);
    }

    public function it_asks_the_pim_location(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $printerAndAsker->askChoiceQuestion('How do you want to install the destination PIM? ', [
            'Using docker-compose',
            'I have an tar.gz archive, install it with docker',
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
