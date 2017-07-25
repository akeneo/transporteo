<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimDetectedToEeAccessPending;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Try the SSH Key already provided.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EeSshKeyAlreadyProvidedTransition implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.ee_try_ssh_key_already_provided' => 'onSshKeyAlreadyProvided',
        ];
    }

    public function onSshKeyAlreadyProvided(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        /** @var OutputInterface $output */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);

        $output->writeln('Enterprise Edition Access Verification with the key you already provided');

        $sshKey = $stateMachine->getGatheredInformation('SshKey');
        $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

        $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);

        try {
            $sshVerificator->verify($sourcePim);
        } catch (EnterpriseEditionAccessException $exception) {
            $output->writeln('It looks like the key you provided is not allowed to download the Enterprise Edition');
            $stateMachine->addToGatheredInformation('EeAccessGranted', false);

            return;
        }

        $stateMachine->addToGatheredInformation('EeAccessGranted', true);
    }
}
