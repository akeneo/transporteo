<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\StructureMigrator;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the structure databases `attribute`, `channel`...
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S070FromDestinationPimFileDatabaseMigratedToDestinationPimStructureMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var StructureMigrator */
    private $structureMigrator;

    public function __construct(
        Translator $translator,
        LoggerInterface $logger,
        StructureMigrator $structureMigrator
    ) {
        parent::__construct($translator, $logger);
        $this->structureMigrator = $structureMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.transporteo.transition.destination_pim_structure_migration' => 'onDestinationPimStructureMigration',
        ];
    }

    public function onDestinationPimStructureMigration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var TransporteoStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_files_migrated_to_destination_pim_structure_migrated.message'));

        $this->structureMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());

        $this->logExit(__FUNCTION__);
    }
}
