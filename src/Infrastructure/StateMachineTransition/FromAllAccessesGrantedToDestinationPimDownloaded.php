<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DestinationPimDownload\DestinationPimDownloadException;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownloaderFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Workflow\Event\Event;

/**
 * Download Destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromAllAccessesGrantedToDestinationPimDownloaded extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    private const DOCKER_COMPOSE_INSTALL = 0;
    private const TAR_GZ_INSTALL = 1;
    private const DESTINATION_PIM_ALREADY_INSTALLED = 2;

    /** @var DestinationPimDownloaderFactory */
    protected $destinationPimDownloaderFactory;

    public function __construct(DestinationPimDownloaderFactory $destinationPimDownloaderFactory)
    {
        $this->destinationPimDownloaderFactory = $destinationPimDownloaderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.announce.download_destination_pim' => 'onDownloadAvailable',
            'workflow.migration_tool.transition.ask_pim_location' => 'onAskPimLocation',
            'workflow.migration_tool.transition.download_destination_pim' => 'onDownloadingTransition',
            'workflow.migration_tool.entered.destination_pim_downloaded' => 'onDestinationDownloaded',
        ];
    }

    public function onDownloadAvailable(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Download : Download your future PIM');
    }

    public function onAskPimLocation(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $destination = $this->printerAndAsker->askChoiceQuestion('How do you want to install the destination PIM? ', [
            'Using docker-compose',
            'I have an tar.gz archive, install it with docker',
            'I have already installed a PIM 2.0',
        ]);

        $destinationPath = null;

        switch ($destination) {
            case self::DOCKER_COMPOSE_INSTALL:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion('Where do you want to install it? ');
                break;
            case self::TAR_GZ_INSTALL:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion('Where is located your archive? ');
                break;
            case self::DESTINATION_PIM_ALREADY_INSTALLED:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion('Where is located your installed pim? ');
                break;
        }

        $stateMachine->setDestinationPimLocation($destination);
        $stateMachine->setDestinationPathPimLocation($destinationPath);
    }

    public function onDownloadingTransition(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $destinationPimLocation = $stateMachine->getDestinationPimLocation();
        $destinationPathPimLocation = $stateMachine->getDestinationPathPimLocation();

        $downloader = null;

        if (self::DESTINATION_PIM_ALREADY_INSTALLED === $destinationPimLocation) {
            $stateMachine->setCurrentDestinationPimLocation($destinationPathPimLocation);

            return;
        }

        switch ($destinationPimLocation) {
            case self::DOCKER_COMPOSE_INSTALL:
                $downloader = $this->destinationPimDownloaderFactory->createGitDestinationPimDownloader();
                break;
            case self::TAR_GZ_INSTALL:
                $downloader = $this->destinationPimDownloaderFactory->createLocalArchiveDestinationPimDownloader($destinationPathPimLocation);
                break;
        }

        try {
            $destinationPim = $downloader->download($stateMachine->getSourcePim(), $stateMachine->getProjectName());
        } catch (\Exception $exception) {
            throw new DestinationPimDownloadException(
                'Impossible to download your PIM : '.$exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        $stateMachine->setCurrentDestinationPimLocation($destinationPim);
    }

    public function onDestinationDownloaded(Event $event)
    {
        $this->printerAndAsker->printMessage('Destination Pim Downloaded : '.$event->getSubject()->getDestinationPim()->getPath());
    }
}
