<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimDetectedToEeAccessPending;

use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Ask for an SSH Key and try it.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EeEmptySshKeyTransition implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
           'workflow.migration_tool.transition.ee_ask_and_try_an_ssh_key' => 'onAskAndTryAnSshKey',
       ];
    }

    public function onAskAndTryAnSshKey(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $input = $stateMachine->getGatheredInformation(InputInterface::class);
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);
        $helper = $stateMachine->getGatheredInformation(QuestionHelper::class);
        $sourcePim = $stateMachine->getGatheredInformation('SourcePim');

        $sshKeyPathQuestion = new Question('Where is located your SSH key allowed to connect to Akeneo Enterprise Edition distribution? ');
        $sshPath = $helper->ask($input, $output, $sshKeyPathQuestion);
        $sshKey = new SshKey($sshPath);
        $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);

        $sshVerificator->verify($sourcePim);
        $stateMachine->addToGatheredInformation('EeAccessGranted', true);
    }
}
