<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurationException;
use Akeneo\PimMigration\Infrastructure\FileFetcherFactory;
use Akeneo\PimMigration\Infrastructure\ImpossibleConnectionException;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S010FromReadyToSourcePimConfigured;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Akeneo\PimMigration\Infrastructure\PimConfiguration\PimConfiguratorFactory;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Akeneo\PimMigration\Infrastructure\MigrationStep\FromReadyToSourcePimConfigured;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * FromReadyToSourcePimConfiguredSpec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S010FromReadyToSourcePimConfiguredSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        LoggerInterface $logger,
        SourcePimConfigurator $sourcePimConfigurator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $logger, $sourcePimConfigurator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S010FromReadyToSourcePimConfigured::class);
    }

    public function it_asks_source_pim_location(
        Event $event,
        TransporteoStateMachine $stateMachine,
        $printerAndAsker,
        $translator
    )
    {
        $event->getSubject()->willReturn($stateMachine);
        $snakeCaseAlphanumeric = 'snake_case, alphanumeric';
        $question = 'What is the name of the project you want to migrate (snake_case, alphanumeric)? ';
        $locationQuestion = 'Where is located your PIM? ';

        $translator->trans('from_ready_to_source_pim_configured.ask_source_pim_location.project_name.question')->willReturn($question);
        $translator->trans('from_ready_to_source_pim_configured.ask_source_pim_location.pim_location.question')->willReturn($locationQuestion);

        $printerAndAsker->askSimpleQuestion(
            $question,
            '',
            Argument::any()
        )->willReturn('a-super-project');
        $printerAndAsker->askChoiceQuestion($locationQuestion, ['locally', 'on a remote server'])->willReturn('locally');

        $stateMachine->setProjectName('a-super-project')->shouldBeCalled();
        $stateMachine->setSourcePimLocation('locally')->shouldBeCalled();


        $this->askSourcePimLocation($event);
    }

    public function it_guards_the_local_source_pim_configuration(
        GuardEvent $guardEvent,
        TransporteoStateMachine $stateMachine
    ) {
        $guardEvent->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePimLocation()->willReturn('locally');
        $guardEvent->setBlocked(false)->shouldBeCalled();

        $this->guardLocalSourcePimConfiguration($guardEvent);

        $stateMachine->getSourcePimLocation()->willReturn('on a remote server');
        $guardEvent->setBlocked(true)->shouldBeCalled();
        $this->guardLocalSourcePimConfiguration($guardEvent);
    }

    public function it_guards_the_distant_source_pim_configuration(
        GuardEvent $guardEvent,
        TransporteoStateMachine $stateMachine
    ) {
        $guardEvent->getSubject()->willReturn($stateMachine);

        $stateMachine->getSourcePimLocation()->willReturn('on a remote server');
        $guardEvent->setBlocked(false)->shouldBeCalled();
        $this->guardDistantSourcePimConfiguration($guardEvent);

        $stateMachine->getSourcePimLocation()->willReturn('locally');
        $guardEvent->setBlocked(true)->shouldBeCalled();
        $this->guardDistantSourcePimConfiguration($guardEvent);
    }

    public function it_configures_a_source_pim_from_a_server(
        Event $event,
        TransporteoStateMachine $stateMachine,
        PimConfiguration $sourcePimConfiguration,
        $sourcePimConfigurator,
        $printerAndAsker,
        $translator
    ) {
        $hostNameQuestion = 'What is the hostname of the source PIM server? ';
        $portQuestion = 'What is the SSH port of the source PIM server? ';
        $sshUserQuestion = 'What is the SSH user you want to connect with ? ';
        $sshKeyPathQuestion = 'What is the absolute path of the private SSH key able to connect to the server? ';
        $composerJsonQuestion = 'What is the absolute path of the composer.json on the server? ';

        $transPrefix = 'from_ready_to_source_pim_configured.on_distant_configuration.';
        $translations = [
            $transPrefix . 'hostname_question' => $hostNameQuestion,
            $transPrefix . 'ssh_port_question' => $portQuestion,
            $transPrefix . 'ssh_user_question' => $sshUserQuestion,
            $transPrefix . 'ssh_key_path_question' => $sshKeyPathQuestion,
            $transPrefix . 'composer_json_path_question' => $composerJsonQuestion
        ];

        foreach ($translations as $translationKey => $translation) {
            $translator->trans($translationKey)->willReturn($translation);
        }

        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getProjectName()->willReturn('a-super-project');

        $printerAndAsker->askSimpleQuestion($hostNameQuestion, Argument::any(), Argument::any())->willReturn('my-super-pim.akeneo.com');
        $printerAndAsker->askSimpleQuestion($portQuestion, '22', Argument::any())->willReturn('22');
        $printerAndAsker->askSimpleQuestion($sshUserQuestion, Argument::any(), Argument::any())->willReturn('akeneo');
        $sshKeyPath = ResourcesFileLocator::getSshKeyPath();

        $printerAndAsker->askSimpleQuestion($sshKeyPathQuestion, Argument::any(), Argument::any())->willReturn($sshKeyPath);

        $sshKey = new SshKey($sshKeyPath);
        $serverAccessInformation = new SshConnection('my-super-pim.akeneo.com', 22, 'akeneo', $sshKey);
        $stateMachine->setSourcePimConnection($serverAccessInformation)->shouldBeCalled();
        $stateMachine->getSourcePimConnection()->willReturn($serverAccessInformation);

        $printerAndAsker->askSimpleQuestion($composerJsonQuestion, Argument::any(), Argument::any())->willReturn(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath());
        $sourcePimServerInformation = new PimServerInformation(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(), 'a-super-project');
        $stateMachine->setSourcePimServerInformation($sourcePimServerInformation)->shouldBeCalled();

        $sourcePimConfigurator->configure($serverAccessInformation, $sourcePimServerInformation)->willReturn($sourcePimConfiguration);
        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration)->shouldBeCalled();

        $this->onDistantConfiguration($event);
    }

    public function it_configures_a_source_pim_from_local(
        Event $event,
        TransporteoStateMachine $stateMachine,
        PimConfiguration $sourcePimConfiguration,
        $sourcePimConfigurator,
        $printerAndAsker,
        $translator
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getProjectName()->willReturn('a-super-project');

        $composerJsonQuestion = 'What is the absolute path of the composer.json on your computer? ';

        $translator->trans('from_ready_to_source_pim_configured.on_local_configuration.composer_json_path_question')->willReturn($composerJsonQuestion);

        $printerAndAsker->askSimpleQuestion($composerJsonQuestion, Argument::any(), Argument::any())->willReturn(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath());

        $sourcePimServerInformation = new PimServerInformation(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(), 'a-super-project');

        $sourcePimConnection = new Localhost();
        $stateMachine->setSourcePimConnection($sourcePimConnection)->shouldBeCalled();
        $stateMachine->getSourcePimConnection()->willReturn($sourcePimConnection);

        $sourcePimConfigurator->configure($sourcePimConnection, $sourcePimServerInformation)->willReturn($sourcePimConfiguration);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration)->shouldBeCalled();
        $stateMachine->setSourcePimServerInformation($sourcePimServerInformation)->shouldBeCalled();

        $this->onLocalConfiguration($event);
    }

    public function it_throws_business_exception_from_technical(
        Event $event,
        TransporteoStateMachine $stateMachine,
        PimConfiguration $sourcePimConfiguration,
        $sourcePimConfigurator,
        $printerAndAsker,
        $translator
    ) {

        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getProjectName()->willReturn('a-super-project');

        $hostNameQuestion = 'What is the hostname of the source PIM server? ';
        $portQuestion = 'What is the SSH port of the source PIM server? ';
        $sshUserQuestion = 'What is the SSH user you want to connect with ? ';
        $sshKeyPathQuestion = 'What is the absolute path of the private SSH key able to connect to the server? ';
        $composerJsonQuestion = 'What is the absolute path of the composer.json on the server? ';

        $transPrefix = 'from_ready_to_source_pim_configured.on_distant_configuration.';
        $translations = [
            $transPrefix . 'hostname_question' => $hostNameQuestion,
            $transPrefix . 'ssh_port_question' => $portQuestion,
            $transPrefix . 'ssh_user_question' => $sshUserQuestion,
            $transPrefix . 'ssh_key_path_question' => $sshKeyPathQuestion,
            $transPrefix . 'composer_json_path_question' => $composerJsonQuestion
        ];

        foreach ($translations as $translationKey => $translation) {
            $translator->trans($translationKey)->willReturn($translation);
        }

        $printerAndAsker->askSimpleQuestion($hostNameQuestion, Argument::any(), Argument::any())->willReturn('my-super-pim.akeneo.com');
        $printerAndAsker->askSimpleQuestion($portQuestion, '22', Argument::any())->willReturn('22');
        $printerAndAsker->askSimpleQuestion($sshUserQuestion, Argument::any(), Argument::any())->willReturn('akeneo');

        $sshKeyPath = ResourcesFileLocator::getSshKeyPath();

        $printerAndAsker->askSimpleQuestion($sshKeyPathQuestion, Argument::any(), Argument::any())->willReturn($sshKeyPath);

        $sshKey = new SshKey($sshKeyPath);
        $serverAccessInformation = new SshConnection('my-super-pim.akeneo.com', 22, 'akeneo', $sshKey);
        $stateMachine->setSourcePimConnection($serverAccessInformation)->shouldBeCalled();
        $stateMachine->getSourcePimConnection()->willReturn($serverAccessInformation);

        $exception = new ImpossibleConnectionException('Impossible to login to akeneo@my-super-pim.akeneo.com:22 using this ssh key : '. $sshKeyPath);

        $printerAndAsker->askSimpleQuestion($composerJsonQuestion, Argument::any(), Argument::any())->willReturn(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath());
        $sourcePimServerInformation = new PimServerInformation(ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(), 'a-super-project');
        $stateMachine->setSourcePimServerInformation($sourcePimServerInformation)->shouldBeCalled();
        $sourcePimConfigurator
            ->configure($serverAccessInformation, $sourcePimServerInformation)
            ->willThrow($exception);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration)->shouldNotBeCalled();

        $this->shouldThrow(new SourcePimConfigurationException($exception->getMessage(), 0, $exception))->during('onDistantConfiguration', [$event]);
    }
}
