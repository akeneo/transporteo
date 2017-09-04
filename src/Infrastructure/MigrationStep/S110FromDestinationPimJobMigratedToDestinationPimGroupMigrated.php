<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration\GroupMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the Group data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S110FromDestinationPimJobMigratedToDestinationPimGroupMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var GroupMigrator */
    private $groupDataMigrator;

    public function __construct(
        Translator $translator,
        GroupMigrator $groupDataMigrator
    ) {
        parent::__construct($translator);
        $this->groupDataMigrator = $groupDataMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_group_migration' => 'onDestinationPimGroupMigration',
        ];
    }

    public function onDestinationPimGroupMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_job_migrated_to_destination_pim_group_migrated.message'));

        $this->groupDataMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
