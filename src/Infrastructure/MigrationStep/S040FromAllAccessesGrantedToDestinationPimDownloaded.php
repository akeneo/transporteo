<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloaderHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloadException;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Archive;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Git;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Local;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Download Destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S040FromAllAccessesGrantedToDestinationPimDownloaded extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    private const DOCKER_COMPOSE_INSTALL = 0;
    private const TAR_GZ_INSTALL = 1;
    private const DESTINATION_PIM_ALREADY_INSTALLED = 2;

    /** @var DestinationPimDownloaderHelper */
    private $destinationPimDownloaderHelper;

    public function __construct(Translator $translator, DestinationPimDownloaderHelper $destinationPimDownloaderHelper)
    {
        parent::__construct($translator);
        $this->translator = $translator;
        $this->destinationPimDownloaderHelper = $destinationPimDownloaderHelper;
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
            $this->translator->trans($transPrefix.'docker_install'),
            $this->translator->trans($transPrefix.'archive_install'),
            $this->translator->trans($transPrefix.'local_install'),
        ];

        $destination = $this->printerAndAsker->askChoiceQuestion(
            $this->translator->trans($transPrefix.'question'),
            $choices
        );

        $destinationPath = null;

        $destination = array_search($destination, $choices);

        switch ($destination) {
            case self::DOCKER_COMPOSE_INSTALL:
                $stateMachine->setDownloadMethod(new Git());
                $stateMachine->setDestinationPimConnection(new DockerConnection());
                break;
            case self::TAR_GZ_INSTALL:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion(
                    $this->translator->trans($transPrefix.'tar_gz_archive_path_question'),
                    '',
                    function ($answer) use ($transPrefix) {
                        $fs = new Filesystem();

                        if (!$fs->isAbsolutePath($answer)) {
                            throw new \RuntimeException(
                                $this->translator->trans(
                                    $transPrefix.'tar_gz_archive_path_error'
                                )
                            );
                        }

                        return $answer;
                    }
                );
                $stateMachine->setDestinationPimConnection(new DockerConnection());
                $stateMachine->setDownloadMethod(new Archive($destinationPath));
                break;
            case self::DESTINATION_PIM_ALREADY_INSTALLED:
                $destinationPath = $this->printerAndAsker->askSimpleQuestion(
                    $this->translator->trans($transPrefix.'local_pim_path_question'),
                    '',
                    function ($answer) use ($transPrefix) {
                        $fs = new Filesystem();

                        if (!$fs->isAbsolutePath($answer)) {
                            throw new \RuntimeException(
                                $this->translator->trans(
                                    $transPrefix.'local_pim_path_error'
                                )
                            );
                        }

                        return $answer;
                    }
                );
                $stateMachine->setDownloadMethod(new Local($destinationPath));
                $stateMachine->setDestinationPimConnection(new Localhost());

                break;
        }
    }

    public function onDownloadingTransition(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $destinationPim = $this->destinationPimDownloaderHelper->download($stateMachine->getSourcePim(), $stateMachine->getProjectName());
        } catch (\Exception $exception) {
            throw new DestinationPimDownloadException(
                $this->translator->trans(
                    'from_all_accesses_granted_to_destination_pim_downloaded.on_downloading.error',
                    [
                        '%exception%' => $exception->getMessage(),
                    ]
                ),
                $exception->getCode(),
                $exception
            );
        }

        $stateMachine->setCurrentDestinationPimLocation($destinationPim);
    }
}
