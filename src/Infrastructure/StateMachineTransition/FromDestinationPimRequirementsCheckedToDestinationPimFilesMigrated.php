<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the table `akeneo_file_storage_file_info`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimRequirementsCheckedToDestinationPimFilesMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var AkeneoFileStorageFileInfoMigrator */
    private $databaseFilesMigrator;

    public function __construct(Translator $translator, AkeneoFileStorageFileInfoMigrator $databaseFilesMigrator)
    {
        parent::__construct($translator);
        $this->databaseFilesMigrator = $databaseFilesMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_files_migration' => 'onDestinationPimFilesMigration',
        ];
    }

    public function onDestinationPimFilesMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage('Migrate your datas file');

        $this->databaseFilesMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
