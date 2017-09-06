<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s030_AccessVerification\AccessException;
use Akeneo\PimMigration\Domain\MigrationStep\s030_AccessVerification\AccessVerificator;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\AccessVerification\SshAccessVerificator;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionVerificatorFactory;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S030FromSourcePimDetectedToAllAccessesGranted;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use Akeneo\PimMigration\Infrastructure\SshKey;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * FromSourcePimDetectedToAllAccessesGrantedSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S030FromSourcePimDetectedToAllAccessesGrantedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        AccessVerificator $enterpriseEditionVerificator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $enterpriseEditionVerificator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S030FromSourcePimDetectedToAllAccessesGranted::class);
    }

    public function it_grants_all_access_to_a_community_edition(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $sourcePim->isEnterpriseEdition()->willReturn(false);

        $event->setBlocked(true)->shouldNotBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_blocks_a_non_ssh_connection(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        Localhost $sourcePimConnection
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $stateMachine->getSourcePimConnection()->willReturn($sourcePimConnection);

        $event->setBlocked(true)->shouldBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_blocks_a_non_authorized_ssh_key(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        SshConnection $pimConnection,
        SshKey $sshKey,
        $enterpriseEditionVerificator,
        $printerAndAsker,
        $translator
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443');

        $stateMachine->getSourcePimConnection()->willReturn($pimConnection);
        $pimConnection->getSshKey()->willReturn($sshKey);

        $serverAccessInformation = SshConnection::fromString('ssh://git@distribution.akeneo.com:443', $sshKey->getWrappedObject());

        $error = 'Enterprise Edition Access Verification with the key you have already provided';
        $translator->trans('from_source_pim_detected_to_all_accesses_granted.on_grant_all_accesses.first_ssh_key_error')->willReturn($error);
        $printerAndAsker->printMessage($error, Argument::any(), Argument::any())->shouldBeCalled();

        $enterpriseEditionVerificator->verify($serverAccessInformation)->willThrow(new AccessException(''));

        $event->setBlocked(true)->shouldBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_allows_an_authorized_ssh_key(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        SshConnection $pimConnection,
        SshKey $sshKey,
        $enterpriseEditionVerificator
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443');

        $stateMachine->getSourcePimConnection()->willReturn($pimConnection);
        $pimConnection->getSshKey()->willReturn($sshKey);

        $serverAccessInformation = SshConnection::fromString('ssh://git@distribution.akeneo.com:443', $sshKey->getWrappedObject());

        $enterpriseEditionVerificator->verify($serverAccessInformation)->shouldBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_asks_ssh_key(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        $printerAndAsker,
        $translator
    ) {
        $event->getSubject()->willReturn($stateMachine);

        $question = 'What is the absolute path of your private SSH key allowed to connect to Akeneo Enterprise Edition distribution? ';
        $translator->trans('from_source_pim_detected_to_all_accesses_granted.on_grant_all_accesses.ssh_key_path_question')->willReturn($question);
        $printerAndAsker->askSimpleQuestion($question, Argument::any(), Argument::any())->willReturn(ResourcesFileLocator::getSshKeyPath());

        $stateMachine->setEnterpriseAccessAllowedKey(new SshKey(ResourcesFileLocator::getSshKeyPath()))->shouldBeCalled();

        $this->onAskAnSshKey($event);
    }

    public function it_grants_an_ee_accesses(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SshKey $sshKey,
        SourcePim $sourcePim,
        $enterpriseEditionVerificator
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443');

        $stateMachine->getEnterpriseAccessAllowedKey()->willReturn($sshKey);

        $serverAccessInformation = SshConnection::fromString('ssh://git@distribution.akeneo.com:443', $sshKey->getWrappedObject());

        $enterpriseEditionVerificator->verify($serverAccessInformation)->shouldBeCalled();

        $this->shouldNotThrow(new AccessException(''))->during('grantEeAccesses', [$event]);
        $this->grantEeAccesses($event);
    }
}
