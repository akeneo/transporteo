<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Domain\StructureMigration\StructureMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the table `akeneo_file_storage_file_info`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimFilesMigratedToDestinationPimStructureMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var StructureMigrator */
    private $structureMigrator;

    public function __construct(
        Translator $translator,
        StructureMigrator $structureMigrator
    ) {
        parent::__construct($translator);
        $this->structureMigrator = $structureMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_structure_migration' => 'onDestinationPimStructureMigration',
        ];
    }

    public function onDestinationPimStructureMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->structureMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
