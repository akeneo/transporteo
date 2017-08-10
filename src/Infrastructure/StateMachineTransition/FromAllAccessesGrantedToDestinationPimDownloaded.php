<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DestinationPimDownload\DestinationPimDownloadException;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownloaderFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;
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

    public function __construct(Translator $translator, DestinationPimDownloaderFactory $destinationPimDownloaderFactory)
    {
        parent::__construct($translator);
        $this->destinationPimDownloaderFactory = $destinationPimDownloaderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.ask_destination_pim_location' => 'onAskDestinationPimLocation',
            'workflow.migration_tool.transition.download_destination_pim' => 'onDownloadingTransition',
        ];
    }

    public function onAskDestinationPimLocation(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $transPrefix = 'from_all_accesses_granted_to_destination_pim_downloaded.on_ask_destination_pim_location.';

        $choices = [
            $this->translator->trans($transPrefix . 'docker_install'),
            $this->translator->trans($transPrefix . 'archive_install'),
            $this->translator->trans($transPrefix . 'local_install'),
        ];

        $destination = $this->printerAndAsker->askChoiceQuestion(
            $this->translator->trans($transPrefix . 'question'),
            $choices
        );

        $destinationPath = null;

        $destination = array_search($destination, $choices);

        switch ($destination) {
            case self::DOCKER_COMPOSE_INSTALL:
                $stateMachine->setUseDocker(true);
                break;
            case self::TAR_GZ_INSTALL:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion(
                    $this->translator->trans($transPrefix . 'tar_gz_archive_path_question'),
                    '',
                    function ($answer) use ($transPrefix) {
                        $fs = new Filesystem();

                        if (!$fs->isAbsolutePath($answer)) {
                            throw new \RuntimeException(
                                $this->translator->trans(
                                    $transPrefix . 'tar_gz_archive_path_error'
                                )
                            );
                        }

                        return $answer;
                    }
                );
                $stateMachine->setUseDocker(true);
                $stateMachine->setDestinationPathPimLocation($destinationPath);
                break;
            case self::DESTINATION_PIM_ALREADY_INSTALLED:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion(
                    $this->translator->trans($transPrefix . 'local_pim_path_question'),
                    '',
                    function ($answer) use ($transPrefix) {
                        $fs = new Filesystem();

                        if (!$fs->isAbsolutePath($answer)) {
                            throw new \RuntimeException(
                                $this->translator->trans(
                                    $transPrefix . 'local_pim_path_error'
                                )
                            );
                        }

                        return $answer;
                    }
                );
                $stateMachine->setUseDocker(false);
                $stateMachine->setDestinationPathPimLocation($destinationPath);
                break;
        }

        $stateMachine->setDestinationPimLocation($destination);
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
                $this->translator->trans(
                    'from_all_accesses_granted_to_destination_pim_downloaded.on_downloading.error',
                    [
                        '%exception%' => $exception->getMessage()
                    ]
                ),
                $exception->getCode(),
                $exception
            );
        }

        $stateMachine->setCurrentDestinationPimLocation($destinationPim);
    }
}
