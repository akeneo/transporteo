<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloaderHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Git;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S040FromAllAccessesGrantedToDestinationPimDownloaded;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * FromReadyToSourcePimConfiguredSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S040FromAllAccessesGrantedToDestinationPimDownloadedSpec extends ObjectBehavior
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
        $this->shouldHaveType(S040FromAllAccessesGrantedToDestinationPimDownloaded::class);
    }

    public function it_asks_the_pim_location_with_docker(
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

        $stateMachine->setDownloadMethod(new Git())->shouldBeCalled();
        $stateMachine->setDestinationPimConnection(new DockerConnection())->shouldBeCalled();

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
