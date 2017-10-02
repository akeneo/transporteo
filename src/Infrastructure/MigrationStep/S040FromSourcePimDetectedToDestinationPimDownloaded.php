<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloaderHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloadException;
use Akeneo\PimMigration\Infrastructure\DestinationPimDownload\Local;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Download Destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S040FromSourcePimDetectedToDestinationPimDownloaded extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var DestinationPimDownloaderHelper */
    private $destinationPimDownloaderHelper;

    public function __construct(Translator $translator, LoggerInterface $logger, DestinationPimDownloaderHelper $destinationPimDownloaderHelper)
    {
        parent::__construct($translator, $logger);
        $this->destinationPimDownloaderHelper = $destinationPimDownloaderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.ask_destination_pim_location' => 'onAskDestinationPimLocation',
            'workflow.transporteo.transition.download_destination_pim' => 'onDownloadingTransition',
        ];
    }

    public function onAskDestinationPimLocation(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $transPrefix = 'from_all_accesses_granted_to_destination_pim_downloaded.on_ask_destination_pim_location.';

        $destinationPath = $this->printerAndAsker->askSimpleQuestion(
            $this->translator->trans($transPrefix.'local_pim_path_question'),
            $stateMachine->getDefaultResponse('installation_path_destination_pim'),
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

        $this->logExit(__FUNCTION__);
    }

    public function onDownloadingTransition(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        try {
            $destinationPim = $this->destinationPimDownloaderHelper->download($stateMachine->getDownloadMethod(), $stateMachine->getSourcePim(), $stateMachine->getProjectName());
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

        $this->logExit(__FUNCTION__);
    }
}
