<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s30_AccessVerification\AccessException;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionVerificatorFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Grant accesses to the PIM..
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S30FromSourcePimDetectedToAllAccessesGranted extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var EnterpriseEditionVerificatorFactory */
    private $enterpriseEditionVerificatorFactory;

    public function __construct(Translator $translator, EnterpriseEditionVerificatorFactory $enterpriseEditionVerificatorFactory)
    {
        parent::__construct($translator);
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

        $sourcePim = $stateMachine->getSourcePim();

        $serverAccessInformation = ServerAccessInformation::fromString($sourcePim->getEnterpriseRepository(), $sshKey);

        $sshVerificator = $this->enterpriseEditionVerificatorFactory->createSshEnterpriseVerificator($serverAccessInformation);

        try {
            $sshVerificator->verify($sourcePim);
        } catch (AccessException $exception) {
            $this->printerAndAsker->printMessage(
                $this->translator->trans(
                    'from_source_pim_detected_to_all_accesses_granted.on_grant_all_accesses.first_ssh_key_error'
                )
            );
            $event->setBlocked(true);
        }
    }

    public function onAskAnSshKey(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $transPrefix = 'from_source_pim_detected_to_all_accesses_granted.on_grant_all_accesses.';

        $sshPath = $this
            ->printerAndAsker
            ->askSimpleQuestion(
                $this->translator->trans($transPrefix.'ssh_key_path_question'),
                '',
                function ($answer) use ($transPrefix) {
                    $fs = new Filesystem();

                    if (!$fs->isAbsolutePath($answer)) {
                        throw new \RuntimeException($this->translator->trans($transPrefix.'ssh_key_path_error'));
                    }

                    return $answer;
                }
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

        $translationPrefix = 'from_source_pim_detected_to_all_accesses_granted.on_grant_all_accesses.';

        $this->printerAndAsker->printMessage(
            $this->translator->trans(
                $translationPrefix.'access_granted',
                [
                    '%edition%' => $this
                        ->translator
                        ->trans(
                            $translationPrefix.($sourcePim->isEnterpriseEdition() ? 'enterprise' : 'community')
                        ),
                ]
            )
        );
    }
}
