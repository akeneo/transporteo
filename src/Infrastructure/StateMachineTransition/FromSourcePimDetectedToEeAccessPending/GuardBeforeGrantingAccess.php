<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimDetectedToEeAccessPending;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Check if CE or the SSH key is already provided.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GuardBeforeGrantingAccess implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.guard.grant_all_accesses' => 'grantAllAccesses',
            'workflow.migration_tool.guard.grant_ee_accesses' => 'grantEeAccesses',
        ];
    }

    public function grantAllAccesses(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

        if (false === $sourcePim->isEnterpriseEdition()) {
            return;
        }

        $sshKey = $stateMachine->getGatheredInformation('SshKey');

        if (null !== $sshKey) {
            $output = $stateMachine->getGatheredInformation(OutputInterface::class);

            $output->writeln('Enterprise Edition Access Verification with the key you already provided');

            $sshKey = $stateMachine->getGatheredInformation('SshKey');
            $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

            $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);

            try {
                $sshVerificator->verify($sourcePim);

                return;
            } catch (EnterpriseEditionAccessException $exception) {
                $output->writeln('It looks like the key you provided is not allowed to download the Enterprise Edition');
            }
        }

        // SshKey not valid or not provided
        $event->setBlocked(true);
    }

    public function grantEeAccesses(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

        $sshKey = $stateMachine->getGatheredInformation('SshKey');

        $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);

        try {
            $sshVerificator->verify($sourcePim);
        } catch (EnterpriseEditionAccessException $exception) {
            $stateMachine->addToGatheredInformation('lastException', $exception);
            $event->setBlocked(true);
        }
    }
}
