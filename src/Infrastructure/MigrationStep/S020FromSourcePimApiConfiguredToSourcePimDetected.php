<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source Pim Detection complete.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S020FromSourcePimApiConfiguredToSourcePimDetected extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.source_pim_detection' => 'onSourcePimDetection',
            'workflow.transporteo.entered.source_pim_detected' => 'onSourcePimDetected',
        ];
    }

    public function onSourcePimDetection(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePimConfiguration = $stateMachine->getSourcePimConfiguration();

        $sourcePim = SourcePim::fromSourcePimConfiguration(
            $stateMachine->getSourcePimConnection(),
            $stateMachine->getSourcePimRealPath(),
            $sourcePimConfiguration,
            $stateMachine->getSourcePimApiParameters()
        );

        $stateMachine->setSourcePim($sourcePim);

        $this->logExit(__FUNCTION__);
    }

    public function onSourcePimDetected(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
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

        $this->logExit(__FUNCTION__);
    }
}
