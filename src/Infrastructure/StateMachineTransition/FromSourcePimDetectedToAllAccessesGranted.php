<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionVerificatorFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Grant accesses to the PIM..
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromSourcePimDetectedToAllAccessesGranted extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var EnterpriseEditionVerificatorFactory */
    private $enterpriseEditionVerificatorFactory;

    public function __construct(EnterpriseEditionVerificatorFactory $enterpriseEditionVerificatorFactory)
    {
        $this->enterpriseEditionVerificatorFactory = $enterpriseEditionVerificatorFactory;
    }

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

        $this->printerAndAsker->printMessage('Enterprise Edition Access Verification with the key you have already provided');

        $sourcePim = $stateMachine->getSourcePim();

        $serverAccessInformation = ServerAccessInformation::fromString($sourcePim->getEnterpriseRepository(), $sshKey);

        $sshVerificator = $this->enterpriseEditionVerificatorFactory->createSshEnterpriseVerificator($serverAccessInformation);

        try {
            $sshVerificator->verify($sourcePim);
        } catch (EnterpriseEditionAccessException $exception) {
            $this->printerAndAsker->printMessage('It looks like the key you have provided is not allowed to download the Enterprise Edition');
            $event->setBlocked(true);
        }
    }

    public function onAskAnSshKey(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $sshPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                sprintf(
                    'What is the %s path of your %s SSH key allowed to connect to Akeneo Enterprise Edition distribution? ',
                    $this->printerAndAsker->getBoldQuestionWords('absolute'),
                    $this->printerAndAsker->getBoldQuestionWords('private')
                )
            );

        $sshKey = new SshKey($sshPath);

        $stateMachine->setSshKey($sshKey);
    }

    public function grantEeAccesses(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $sourcePim = $stateMachine->getSourcePim();

        $sshKey = $stateMachine->getSshKey();
        $serverAccessInformation = ServerAccessInformation::fromString($sourcePim->getEnterpriseRepository(), $sshKey);

        $sshVerificator = $this->enterpriseEditionVerificatorFactory->createSshEnterpriseVerificator($serverAccessInformation);
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
