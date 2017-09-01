<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration\ReferenceDataMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate extra data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S130FromDestinationPimExtraDataMigratedToDestinationPimReferenceDataMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var ReferenceDataMigrator */
    private $referenceDataMigrator;

    public function __construct(
        Translator $translator,
        ReferenceDataMigrator $referenceDataMigrator
    ) {
        parent::__construct($translator);
        $this->referenceDataMigrator = $referenceDataMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_reference_data_migration' => 'onDestinationPimReferenceDataMigration',
        ];
    }

    public function onDestinationPimReferenceDataMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_extra_data_migrated_to_destination_pim_reference_data_migrated.message'));

        $this->referenceDataMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
