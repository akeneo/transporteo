<?php

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration\ProductDraftMigrator;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate products draft and proposal
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S160FromDestinationPimProductVariationMigratedToDestinationPimProductDraftMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var ProductDraftMigrator */
    private $productDraftMigrator;

    public function __construct(
        TranslatorInterface $translator,
        LoggerInterface $logger,
        ProductDraftMigrator $productDraftMigrator
    ) {
        parent::__construct($translator, $logger);

        $this->productDraftMigrator = $productDraftMigrator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.destination_pim_product_draft_migration' => 'onDestinationPimProductDraftMigration',
        ];
    }

    public function onDestinationPimProductDraftMigration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_product_variation_migrated_to_destination_pim_product_draft_migrated.message'));

        $this->productDraftMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());

        $this->logExit(__FUNCTION__);
    }
}
