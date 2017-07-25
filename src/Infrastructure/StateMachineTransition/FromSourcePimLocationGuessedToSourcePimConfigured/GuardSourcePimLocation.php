<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimLocationGuessedToSourcePimConfigured;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Guard to decide the location.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GuardSourcePimLocation implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.guard.local_source_pim_configuration' => 'guardLocalSourcePimConfiguration',
            'workflow.migration_tool.guard.distant_source_pim_configuration' => 'guardDistantSourcePimConfiguration',
        ];
    }

    public function guardLocalSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getGatheredInformation('PimSourceLocation');

        $event->setBlocked($pimSourceLocation !== 'local');
    }

    public function guardDistantSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getGatheredInformation('PimSourceLocation');

        $event->setBlocked($pimSourceLocation !== 'distant');
    }
}
