<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DatabaseServices\ConnectionBuilder;
use Akeneo\PimMigration\Domain\FilesMigration\AkeneoFileStorageFileInfoMigrator;
use Akeneo\PimMigration\Infrastructure\AkeneoFileStorageFileInfoMigratorFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\NaiveMigratorFactory;
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

    /** @var ConnectionBuilder */
    private $connectionBuilder;

    /** @var AkeneoFileStorageFileInfoMigratorFactory */
    private $akeneoFileStorageFileInfoMigratorFactory;

    /** @var NaiveMigratorFactory */
    private $naiveMigratorFactory;

    public function __construct(
        Translator $translator,
        AkeneoFileStorageFileInfoMigratorFactory $akeneoFileStorageFileInfoMigratorFactory,
        NaiveMigratorFactory $naiveMigratorFactory,
        ConnectionBuilder $connectionBuilder
    ) {
        parent::__construct($translator);
        $this->connectionBuilder = $connectionBuilder;
        $this->akeneoFileStorageFileInfoMigratorFactory = $akeneoFileStorageFileInfoMigratorFactory;
        $this->naiveMigratorFactory = $naiveMigratorFactory;
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

        $naiveMigrator = $this->naiveMigratorFactory->createNaiveMigrator($this->connectionBuilder);

        $akeneoFileStorageIngoMigrator = $this
            ->akeneoFileStorageFileInfoMigratorFactory
            ->createFileStorageFileInfoMigrator($naiveMigrator);

        $akeneoFileStorageIngoMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
