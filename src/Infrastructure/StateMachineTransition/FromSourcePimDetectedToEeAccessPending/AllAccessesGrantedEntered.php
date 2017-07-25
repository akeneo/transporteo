<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimDetectedToEeAccessPending;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * All Accesses are granted.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AllAccessesGrantedEntered implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.entered.all_accesses_granted' => 'onAllAccessesGranted',
        ];
    }

    public function onAllAccessesGranted(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        /** @var OutputInterface $output */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);

        $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

        $output->writeln(
            sprintf(
                'Access to the %s edition allowed',
                $sourcePim->isEnterpriseEdition() ? 'Enterprise' : 'Community'
            )
        );
    }
}
