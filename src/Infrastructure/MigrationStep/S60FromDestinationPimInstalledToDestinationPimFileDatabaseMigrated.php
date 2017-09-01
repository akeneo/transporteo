<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s60_FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the table `akeneo_file_storage_file_info`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S60FromDestinationPimInstalledToDestinationPimFileDatabaseMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var AkeneoFileStorageFileInfoMigrator */
    private $akeneoFileStorageFileInfoMigrator;

    public function __construct(
        Translator $translator,
        AkeneoFileStorageFileInfoMigrator $akeneoFileStorageFileInfoMigrator
    ) {
        parent::__construct($translator);
        $this->akeneoFileStorageFileInfoMigrator = $akeneoFileStorageFileInfoMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_file_database_migration' => 'onDestinationPimFileDatabaseMigration',
        ];
    }

    public function onDestinationPimFileDatabaseMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_requirements_checked_to_destination_pim_files_database_migrated.message'));

        $this->akeneoFileStorageFileInfoMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
