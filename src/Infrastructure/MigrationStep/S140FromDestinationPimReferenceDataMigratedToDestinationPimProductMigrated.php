<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration\ProductMigrator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the products data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S140FromDestinationPimReferenceDataMigratedToDestinationPimProductMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var ProductMigrator */
    private $productMigrator;

    public function __construct(Translator $translator, ProductMigrator $productMigrator)
    {
        parent::__construct($translator);

        $this->productMigrator = $productMigrator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_product_migration' => 'onDestinationPimProductMigration',
        ];
    }

    public function onDestinationPimProductMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_reference_data_migrated_to_destination_pim_product_migrated.message'));

        $this->productMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
