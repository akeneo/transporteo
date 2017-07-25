<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimConfiguredToSourcePimDetected;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source Pim Detection complete.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimDetected implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.entered.source_pim_detected' => 'onSourcePimDetected',
        ];
    }

    public function onSourcePimDetected(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        /** @var OutputInterface $output */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);
        $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

        $output->writeln(sprintf(
            'You want to migrate from an edition %s with %s storage%s',
            $sourcePim->isEnterpriseEdition() ? 'Enterprise' : 'Community',
            null === $sourcePim->getMongoDatabase() ? 'ORM' : 'Hybrid',
            $sourcePim->hasIvb() ? 'with InnerVariationBundle.' : '.'
        ));

        $output->writeln('Source Pim Detection : Successful');
    }
}
