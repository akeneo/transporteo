<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

class FromSourcePimDetectedToAllAccessesGranted extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.guard.grant_all_accesses' => 'grantAllAccesses',
            'workflow.migration_tool.transition.ee_ask_an_ssh_key' => 'onAskAnSshKey',
            'workflow.migration_tool.guard.grant_ee_accesses' => 'grantEeAccesses',
            'workflow.migration_tool.entered.all_accesses_granted' => 'onAllAccessesGranted',
        ];
    }

    public function grantAllAccesses(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePim = $stateMachine->getSourcePim();

        if (false === $sourcePim->isEnterpriseEdition()) {
            return;
        }

        $sshKey = $stateMachine->getSshKey();

        if (null === $sshKey) {
            $event->setBlocked(true);

            return;
        }

        $this->printerAndAsker->printMessage('Enterprise Edition Access Verification with the key you already provided');

        $sourcePim = $stateMachine->getSourcePim();

        $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);

        try {
            $sshVerificator->verify($sourcePim);
        } catch (EnterpriseEditionAccessException $exception) {
            $this->printerAndAsker->printMessage('It looks like the key you provided is not allowed to download the Enterprise Edition');
            $event->setBlocked(true);
        }
    }

    public function onAskAnSshKey(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sshPath = $this->printerAndAsker->askSimpleQuestion('Where is located your SSH key allowed to connect to Akeneo Enterprise Edition distribution? ');

        $sshKey = new SshKey($sshPath);

        $stateMachine->setSshKey($sshKey);
    }

    public function grantEeAccesses(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $sourcePim = $stateMachine->getSourcePim();

        $sshKey = $stateMachine->getSshKey();

        $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);
        $sshVerificator->verify($sourcePim);
    }

    public function onAllAccessesGranted(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePim = $stateMachine->getSourcePim();

        $this->printerAndAsker->printMessage(
            sprintf(
                'Access to the %s edition allowed',
                $sourcePim->isEnterpriseEdition() ? 'Enterprise' : 'Community'
            )
        );
    }
}
