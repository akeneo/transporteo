<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimConfiguredToSourcePimDetected;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source Pim Detection ready to begin.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimDetectionAnnounced implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.announce.source_pim_detection' => 'onDetectionAvailable',
        ];
    }

    public function onDetectionAvailable(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        /** @var OutputInterface $output */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);

        $output->writeln('Source Pim Detection : Detect your source pim');
    }
}
