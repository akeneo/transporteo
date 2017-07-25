<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshFileFetcher;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;

/**
 * Ask for the location of the Source Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromReadyToSourcePimConfigured extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.leave.ready' => 'leaveReadyPlace',
            'workflow.migration_tool.transition.ask_source_pim_location' => 'askSourcePimLocation',
            'workflow.migration_tool.guard.local_source_pim_configuration' => 'guardLocalSourcePimConfiguration',
            'workflow.migration_tool.guard.distant_source_pim_configuration' => 'guardDistantSourcePimConfiguration',
            'workflow.migration_tool.transition.distant_source_pim_configuration' => 'onDistantConfiguration',
            'workflow.migration_tool.transition.local_source_pim_configuration' => 'onLocalConfiguration',
        ];
    }

    public function leaveReadyPlace(Event $event)
    {
        $this->output->writeln('Here you are ! Few questions before start to migrate the PIM !');
    }

    public function askSourcePimLocation(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $projectNameQuestion = new Question('What is the name of the project you want to migrate? ');
        $stateMachine->setProjectName($this->ask($projectNameQuestion));

        $pimLocationQuestion = new ChoiceQuestion('Where is located your PIM? ', ['local', 'server']);
        $stateMachine->setSourcePimLocation($this->ask($pimLocationQuestion));
    }

    public function guardLocalSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $event->setBlocked($pimSourceLocation !== 'local');
    }

    public function guardDistantSourcePimConfiguration(GuardEvent $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $pimSourceLocation = $stateMachine->getSourcePimLocation();

        $event->setBlocked($pimSourceLocation !== 'distant');
    }

    public function onDistantConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->output->writeln('Source Pim Configuration: Collect your configuration files from a server');

        $hostQuestion = new Question('What is the hostname of the source PIM server? ');
        $host = $this->ask($hostQuestion);

        $portQuestion = new Question('What is the SSH port of the source PIM server? ', 22);
        $port = $this->ask($portQuestion);

        $userNameQuestion = new Question('What is the SSH user you want to connect with ? ');
        $user = $this->ask($userNameQuestion);

        $sshKeyPathQuestion = new Question('Where is located the private SSH key able to connect to the server ? ');
        $sshPath = $this->ask($sshKeyPathQuestion);

        $sshKeySourcePimServer = new SshKey($sshPath);
        $stateMachine->setSshKey($sshKeySourcePimServer);
        $serverAccessInformation = new ServerAccessInformation($host, $port, $user, $sshKeySourcePimServer);

        $composerJsonPathQuestion = new Question('Where is located the composer.json on the server? ');
        //TODO REMOVE THAT ONLY TEST
        $composerJsonPathQuestion->setAutocompleterValues([
            '/home/docker/migration/tests/resources/step_one_source_pim_configuration/community_standard/composer.json',
            '/home/docker/migration/tests/resources/step_one_source_pim_configuration/enterprise_mongo_ivb_standard/composer.json',
        ]);

        $composerJsonPath = $this->ask($composerJsonPathQuestion);
        $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());

        $sourcePimConfigurator = new SourcePimConfigurator(new SshFileFetcher($serverAccessInformation));
        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);
    }

    public function onLocalConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        $this->output->writeln('Source Pim Configuration: Collect your configuration files from your computer');

        $composerJsonPathQuestion = new Question('Where is located the composer.json on your computer? ');

        //TODO REMOVE THAT ONLY TEST
        $composerJsonPathQuestion->setAutocompleterValues([
            '/home/docker/migration/tests/resources/step_one_source_pim_configuration/community_standard/composer.json',
            '/home/docker/migration/tests/resources/step_one_source_pim_configuration/enterprise_mongo_ivb_standard/composer.json',
        ]);

        $composerJsonPath = $this->ask($composerJsonPathQuestion);

        $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getProjectName());

        $sourcePimConfigurator = new SourcePimConfigurator(new LocalFileFetcher());
        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        $stateMachine->setSourcePimConfiguration($sourcePimConfiguration);
    }
}
