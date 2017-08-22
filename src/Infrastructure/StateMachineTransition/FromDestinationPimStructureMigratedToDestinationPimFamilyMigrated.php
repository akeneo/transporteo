<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\FamilyMigration\FamilyDataMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the family data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimStructureMigratedToDestinationPimFamilyMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var FamilyDataMigrator */
    private $familyDataMigrator;

    public function __construct(
        Translator $translator,
        FamilyDataMigrator $familyDataMigrator
    ) {
        parent::__construct($translator);
        $this->familyDataMigrator = $familyDataMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_family_migration' => 'onDestinationPimFamilyMigration',
        ];
    }

    public function onDestinationPimFamilyMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->familyDataMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
