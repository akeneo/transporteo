<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\SystemMigration\SystemMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the system data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimFamilyMigratedToDestinationPimSystemMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var SystemMigrator */
    private $systemMigrator;

    public function __construct(
        Translator $translator,
        SystemMigrator $systemMigrator
    ) {
        parent::__construct($translator);
        $this->systemMigrator = $systemMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_system_migration' => 'onDestinationPimSystemMigration',
        ];
    }

    public function onDestinationPimSystemMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->systemMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
