<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the job data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S100FromDestinationPimSystemMigratedToDestinationPimJobMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var JobMigrator */
    private $jobMigrator;

    public function __construct(
        Translator $translator,
        LoggerInterface $logger,
        JobMigrator $jobMigrator
    ) {
        parent::__construct($translator, $logger);
        $this->jobMigrator = $jobMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_job_migration' => 'onDestinationPimJobMigration',
        ];
    }

    public function onDestinationPimJobMigration(Event $event)
    {
        $this->logEntering(__FUNCTION__);

        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_system_migrated_to_destination_pim_job_migrated.message'));

        $this->jobMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());

        $this->logExit(__FUNCTION__);
    }
}
