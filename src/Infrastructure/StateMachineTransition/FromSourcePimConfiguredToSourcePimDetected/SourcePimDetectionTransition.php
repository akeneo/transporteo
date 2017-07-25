<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimConfiguredToSourcePimDetected;

use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source Pim Detection in progress.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimDetectionTransition implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.source_pim_detection' => 'onSourcePimDetection',
        ];
    }

    public function onSourcePimDetection(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePimConfiguration = $stateMachine->getGatheredInformation('SourcePimConfiguration');

        $sourcePim = SourcePim::fromSourcePimConfiguration($sourcePimConfiguration);

        $stateMachine->addToGatheredInformation('SourcePim', $sourcePim);
    }
}
