<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimLocationGuessedToSourcePimConfigured;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshFileFetcher;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Configuration of a distant PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DistantSourcePimConfigurationTransition implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.distant_source_pim_configuration' => 'onConfiguration',
        ];
    }

    public function onConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        /** @var InputInterface $input */
        $input = $stateMachine->getGatheredInformation(InputInterface::class);
        /** @var OutputInterface $ouput */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);
        /** @var QuestionHelper $questionHelper */
        $helper = $stateMachine->getGatheredInformation(QuestionHelper::class);

        $output->writeln('Source Pim Configuration: Collect your configuration files from a server');

        $hostQuestion = new Question('What is the hostname of the source PIM server? ');
        $host = $helper->ask($input, $output, $hostQuestion);

        $portQuestion = new Question('What is the SSH port of the source PIM server? ', 22);
        $port = $helper->ask($input, $output, $portQuestion);

        $userNameQuestion = new Question('What is the SSH user you want to connect with ? ');
        $user = $helper->ask($input, $output, $userNameQuestion);

        $sshKeyPathQuestion = new Question('Where is located the private SSH key able to connect to the server ? ');
        $sshPath = $helper->ask($input, $output, $sshKeyPathQuestion);

        $sshKeySourcePimServer = new SshKey($sshPath);
        $serverAccessInformation = new ServerAccessInformation($host, $port, $user, $sshKeySourcePimServer);

        $composerJsonPathQuestion = new Question('Where is located the composer.json on the server? ');

        $composerJsonPath = $helper->ask($input, $output, $composerJsonPathQuestion);
        $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getGatheredInformation('ProjectName'));

        $sourcePimConfigurator = new SourcePimConfigurator(new SshFileFetcher($serverAccessInformation));
        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        $stateMachine->addToGatheredInformation('SshKey', $sshKeySourcePimServer);
        $stateMachine->addToGatheredInformation('SourcePimConfiguration', $sourcePimConfiguration);
    }
}
