<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source Pim Detection complete.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S020FromSourcePimConfiguredToSourcePimDetected extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.source_pim_detection' => 'onSourcePimDetection',
            'workflow.migration_tool.entered.source_pim_detected' => 'onSourcePimDetected',
        ];
    }

    public function onSourcePimDetection(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePimConfiguration = $stateMachine->getSourcePimConfiguration();

        $sourcePim = SourcePim::fromSourcePimConfiguration($stateMachine->getSourcePimConnection(), $stateMachine->getSourcePimRealPath(), $sourcePimConfiguration);

        $stateMachine->setSourcePim($sourcePim);
    }

    public function onSourcePimDetected(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePim = $stateMachine->getSourcePim();

        $transPrefix = 'from_source_pim_configured_to_source_pim_detected.on_source_pim_detected.';

        $editionTrans = $transPrefix.(true === $sourcePim->isEnterpriseEdition() ? 'an_enterprise' : 'a_community');
        $storageTrans = $transPrefix.(null === $sourcePim->getMongoDatabase() ? 'orm' : 'hybrid');

        $this->printerAndAsker->printMessage(
            $this->translator->trans(
                $transPrefix.'result',
                [
                    '%edition%' => $this->translator->trans($editionTrans),
                    '%storage%' => $this->translator->trans($storageTrans),
                    '%inner%' => $sourcePim->hasIvb() ? $this->translator->trans($transPrefix.'and_inner_variation_bundle') : '',
                ]
            )
        );
    }
}
