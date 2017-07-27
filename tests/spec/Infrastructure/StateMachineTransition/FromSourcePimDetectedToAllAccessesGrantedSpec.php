<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionVerificatorFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshKey;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * FromSourcePimDetectedToAllAccessesGrantedSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromSourcePimDetectedToAllAccessesGrantedSpec extends ObjectBehavior
{
    public function let(EnterpriseEditionVerificatorFactory $enterpriseEditionVerificatorFactory, PrinterAndAsker $printerAndAsker)
    {
        $this->beConstructedWith($enterpriseEditionVerificatorFactory);
        $this->setPrinterAndAsker($printerAndAsker);
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

    public function it_blocks_a_non_existing_ssh_key(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $stateMachine->getSshKey()->willReturn(null);

        $event->setBlocked(true)->shouldBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_blocks_a_non_authorized_ssh_key(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        SshKey $sshKey,
        SshEnterpriseEditionAccessVerificator $sshEnterpriseEditionAccessVerificator,
        $enterpriseEditionVerificatorFactory,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443');

        $serverAccessInformation = ServerAccessInformation::fromString('ssh://git@distribution.akeneo.com:443', $sshKey->getWrappedObject());

        $stateMachine->getSshKey()->willReturn($sshKey);

        $printerAndAsker->printMessage('Enterprise Edition Access Verification with the key you already provided')->shouldBeCalled();
        $enterpriseEditionVerificatorFactory->createSshEnterpriseVerificator($serverAccessInformation)->willReturn($sshEnterpriseEditionAccessVerificator);

        $sshEnterpriseEditionAccessVerificator->verify($sourcePim)->willThrow(new EnterpriseEditionAccessException(''));
        $printerAndAsker->printMessage('It looks like the key you provided is not allowed to download the Enterprise Edition')->shouldBeCalled();
        $event->setBlocked(true)->shouldBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_allows_an_authorized_ssh_key(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        SshKey $sshKey,
        SshEnterpriseEditionAccessVerificator $sshEnterpriseEditionAccessVerificator,
        $enterpriseEditionVerificatorFactory,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);

        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443');

        $stateMachine->getSshKey()->willReturn($sshKey);

        $serverAccessInformation = ServerAccessInformation::fromString('ssh://git@distribution.akeneo.com:443', $sshKey->getWrappedObject());

        $printerAndAsker->printMessage('Enterprise Edition Access Verification with the key you already provided')->shouldBeCalled();
        $enterpriseEditionVerificatorFactory->createSshEnterpriseVerificator($serverAccessInformation)->willReturn($sshEnterpriseEditionAccessVerificator);

        $sshEnterpriseEditionAccessVerificator->verify($sourcePim)->shouldBeCalled();

        $this->grantAllAccesses($event);
    }

    public function it_asks_ssh_key(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $printerAndAsker->askSimpleQuestion('Where is located your SSH key allowed to connect to Akeneo Enterprise Edition distribution? ')->willReturn(ResourcesFileLocator::getSshKeyPath());

        $stateMachine->setSshKey(new SshKey(ResourcesFileLocator::getSshKeyPath()))->shouldBeCalled();

        $this->onAskAnSshKey($event);
    }

    public function it_grants_an_ee_accesses(
        GuardEvent $event,
        MigrationToolStateMachine $stateMachine,
        SshKey $sshKey,
        EnterpriseEditionAccessVerificator $enterpriseEditionVerificator,
        SourcePim $sourcePim,
        $enterpriseEditionVerificatorFactory
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getSshKey()->willReturn($sshKey);

        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443');

        $serverAccessInformation = ServerAccessInformation::fromString('ssh://git@distribution.akeneo.com:443', $sshKey->getWrappedObject());

        $enterpriseEditionVerificatorFactory->createSshEnterpriseVerificator($serverAccessInformation)->willReturn($enterpriseEditionVerificator);
        $enterpriseEditionVerificator->verify($sourcePim)->shouldBeCalled();

        $this->shouldNotThrow(new EnterpriseEditionAccessException(''))->during('grantEeAccesses', [$event]);
        $this->grantEeAccesses($event);
    }
}
