<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration\SystemMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the system data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S090FromDestinationPimFamilyMigratedToDestinationPimSystemMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var SystemMigrator */
    private $systemMigrator;

    public function __construct(
        Translator $translator,
        LoggerInterface $logger,
        SystemMigrator $systemMigrator
    ) {
        parent::__construct($translator, $logger);
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
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_family_migrated_to_destination_pim_system_migrated.message'));

        $this->systemMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());

        $this->logExit(__FUNCTION__);
    }
}
