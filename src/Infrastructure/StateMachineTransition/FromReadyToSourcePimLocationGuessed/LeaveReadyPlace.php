<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromReadyToSourcePimLocationGuessed;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Start the State machine.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LeaveReadyPlace implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.leave.ready' => 'leaveReadyPlace',
        ];
    }

    public function leaveReadyPlace(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        /** @var OutputInterface $ouput */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);

        $output->writeln('Here you are ! Few questions before start to migrate the PIM !');
    }
}
