<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfiguration;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SourcePimConfiguration\SourcePimConfiguratorFactory;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromReadyToSourcePimConfigured;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * FromReadyToSourcePimConfiguredSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromReadyToSourcePimConfiguredSpec extends ObjectBehavior
{
    public function let(
        FileFetcherFactory $fileFetcherFactory,
        SourcePimConfiguratorFactory $sourcePimConfiguratorFactory,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($fileFetcherFactory, $sourcePimConfiguratorFactory);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FromReadyToSourcePimConfigured::class);
    }

    public function it_ask_source_pim_location(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        $printerAndAsker
    )
    {
        $event->getSubject()->willReturn($stateMachine);
        $printerAndAsker->askSimpleQuestion('What is the name of the project you want to migrate? ')->willReturn('a-super-project');
        $printerAndAsker->askChoiceQuestion('Where is located your PIM? ', ['local', 'server'])->willReturn('local');

        $stateMachine->setProjectName('a-super-project')->shouldBeCalled();
        $stateMachine->setSourcePimLocation('local')->shouldBeCalled();


        $this->askSourcePimLocation($event);
    }

    public function it_guards_the_local_source_pim_configuration(
        GuardEvent $guardEvent,
        MigrationToolStateMachine $stateMachine
    ) {
        $guardEvent->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePimLocation()->willReturn('local');
        $guardEvent->setBlocked(false)->shouldBeCalled();

        $this->guardLocalSourcePimConfiguration($guardEvent);

        $stateMachine->getSourcePimLocation()->willReturn('server');
        $guardEvent->setBlocked(true)->shouldBeCalled();
        $this->guardLocalSourcePimConfiguration($guardEvent);
    }

    public function it_guards_the_distant_source_pim_configuration(
        GuardEvent $guardEvent,
        MigrationToolStateMachine $stateMachine
    ) {
        $guardEvent->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePimLocation()->willReturn('server');
        $guardEvent->setBlocked(false)->shouldBeCalled();
        $this->guardDistantSourcePimConfiguration($guardEvent);

        $stateMachine->getSourcePimLocation()->willReturn('local');
        $guardEvent->setBlocked(true)->shouldBeCalled();
        $this->guardDistantSourcePimConfiguration($guardEvent);
    }

    public function it_configure_a_source_pim_from_a_server(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        FileFetcher $fileFetcher,
        SourcePimConfigurator $sourcePimConfigurator,
        SourcePimConfiguration $sourcePimConfiguration,
        $fileFetcherFactory,
        $sourcePimConfiguratorFactory,
        $printerAndAsker
    ) {
        $printerAndAsker->printMessage('Source Pim Configuration: Collect your configuration files from a server')->shouldBeCalled();

        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getProjectName()->willReturn('a-super-project');

        $printerAndAsker->askSimpleQuestion('What is the hostname of the source PIM server? ')->willReturn('my-super-pim.akeneo.com');
        $printerAndAsker->askSimpleQuestion('What is the SSH port of the source PIM server? ', '22')->willReturn('22');
        $printerAndAsker->askSimpleQuestion('What is the SSH user you want to connect with ? ')->willReturn('akeneo');
        $printerAndAsker->askSimpleQuestion('Where is located the private SSH key able to connect to the server? ')->willReturn('/home/docker/migration/tests/resources/a_false_ssh_key');

        $sshKey = new SshKey(ResourcesFileLocator::getSshKeyPath());
        $stateMachine->setSshKey($sshKey)->shouldBeCalled();
        $serverAccessInformation = new ServerAccessInformation('my-super-pim.akeneo.com', 22, 'akeneo', $sshKey);

        $printerAndAsker->askSimpleQuestion('Where is located the composer.json on the server? ')->willReturn(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath());
        $fileFetcherFactory->createSshFileFetcher($serverAccessInformation)->willReturn($fileFetcher);
        $sourcePimConfiguratorFactory->createSourcePimConfigurator($fileFetcher)->willReturn($sourcePimConfigurator);
        $sourcePimConfigurator->configure(new PimServerInformation(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(), 'a-super-project'))->willReturn($sourcePimConfiguration);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration)->shouldBeCalled();

        $this->onDistantConfiguration($event);
    }

    public function it_configure_a_source_pim_from_local(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        FileFetcher $fileFetcher,
        SourcePimConfigurator $sourcePimConfigurator,
        SourcePimConfiguration $sourcePimConfiguration,
        $fileFetcherFactory,
        $sourcePimConfiguratorFactory,
        $printerAndAsker
    ) {
        $printerAndAsker->printMessage('Source Pim Configuration: Collect your configuration files from your computer')->shouldBeCalled();
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getProjectName()->willReturn('a-super-project');

        $printerAndAsker->askSimpleQuestion('Where is located the composer.json on your computer? ')->willReturn(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath());

        $fileFetcherFactory->createLocalFileFetcher()->willReturn($fileFetcher);
        $sourcePimConfiguratorFactory->createSourcePimConfigurator($fileFetcher)->willReturn($sourcePimConfigurator);

        $sourcePimConfigurator->configure(new PimServerInformation(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(), 'a-super-project'))->willReturn($sourcePimConfiguration);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration)->shouldBeCalled();

        $this->onLocalConfiguration($event);
    }
}
