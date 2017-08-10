<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source Pim Detection complete.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromSourcePimConfiguredToSourcePimDetected extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.announce.source_pim_detection' => 'onDetectionAvailable',
            'workflow.migration_tool.transition.source_pim_detection' => 'onSourcePimDetection',
            'workflow.migration_tool.entered.source_pim_detected' => 'onSourcePimDetected',
        ];
    }

    public function onDetectionAvailable(Event $event)
    {
        $this->printerAndAsker->printMessage('Source Pim Detection : Detect your source pim');
    }

    public function onSourcePimDetection(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePimConfiguration = $stateMachine->getSourcePimConfiguration();

        $sourcePim = SourcePim::fromSourcePimConfiguration($sourcePimConfiguration);

        $stateMachine->setSourcePim($sourcePim);
    }

    public function onSourcePimDetected(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePim = $stateMachine->getSourcePim();

        $this->printerAndAsker->printMessage(sprintf(
            'You want to migrate from an edition %s with %s storage%s',
            $sourcePim->isEnterpriseEdition() ? 'Enterprise' : 'Community',
            null === $sourcePim->getMongoDatabase() ? 'ORM' : 'Hybrid',
            $sourcePim->hasIvb() ? ' with InnerVariationBundle.' : '.'
        ));

        $this->printerAndAsker->printMessage('Source Pim Detection : Complete');
    }
}
