<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrates the products variations (IVB and/or variant-group)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S150FromDestinationPimProductMigratedToDestinationPimProductVariationMigrated  extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger)
    {
        parent::__construct($translator, $logger);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.destination_pim_product_variation_migration' => 'onDestinationPimProductVariationMigration',
        ];
    }

    public function onDestinationPimProductVariationMigration(Event $event)
    {
        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_product_migrated_to_destination_pim_product_variation_migrated.message'));
    }
}
