<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimLocationGuessedToSourcePimConfigured;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source PIM configuration successful.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimConfigured implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.entered.source_pim_configured' => 'onSourcePimConfigured',
        ];
    }

    public function onSourcePimConfigured(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        /** @var OutputInterface $output */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);

        $output->writeln('Source Pim Configuration : Successful');
    }
}
