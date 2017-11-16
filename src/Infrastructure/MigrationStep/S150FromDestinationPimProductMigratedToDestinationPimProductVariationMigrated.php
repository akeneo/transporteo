<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidProductVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrates the products variations (IVB and/or variant-group).
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S150FromDestinationPimProductMigratedToDestinationPimProductVariationMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var ProductVariationMigrator */
    private $productVariationMigrator;

    public function __construct(Translator $translator, LoggerInterface $logger, ProductVariationMigrator $productVariationMigrator)
    {
        parent::__construct($translator, $logger);

        $this->productVariationMigrator = $productVariationMigrator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.destination_pim_product_variation_migration' => 'onDestinationPimProductVariationMigration',
        ];
    }

    public function onDestinationPimProductVariationMigration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_product_migrated_to_destination_pim_product_variation_migrated.message'));

        try {
            $this->productVariationMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
        } catch (InvalidProductVariationException $exception) {
            foreach ($exception->getMessages() as $message) {
                $this->printerAndAsker->warning($message);
            }
        }

        $this->logExit(__FUNCTION__);
    }
}
