<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\JobMigration\JobMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate the job data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimSystemMigratedToDestinationPimJobMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var JobMigrator */
    private $jobMigrator;

    public function __construct(
        Translator $translator,
        JobMigrator $jobMigrator
    ) {
        parent::__construct($translator);
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
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->jobMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());
    }
}
