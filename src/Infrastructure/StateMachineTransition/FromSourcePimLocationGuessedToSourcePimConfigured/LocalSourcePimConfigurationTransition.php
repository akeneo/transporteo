<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromSourcePimLocationGuessedToSourcePimConfigured;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Configuration of a local PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalSourcePimConfigurationTransition implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.local_source_pim_configuration' => 'onLocalConfiguration',
        ];
    }

    public function onLocalConfiguration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        /** @var InputInterface $input */
        $input = $stateMachine->getGatheredInformation(InputInterface::class);
        /** @var OutputInterface $ouput */
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);
        /** @var QuestionHelper $questionHelper */
        $helper = $stateMachine->getGatheredInformation(QuestionHelper::class);

        $output->writeln('Source Pim Configuration: Collect your configuration files from your computer');

        $composerJsonPathQuestion = new Question('Where is located the composer.json on your computer? ');
        $composerJsonPath = $helper->ask($input, $output, $composerJsonPathQuestion);

        $pimServerInformation = new PimServerInformation($composerJsonPath, $stateMachine->getGatheredInformation('ProjectName'));

        $sourcePimConfigurator = new SourcePimConfigurator(new LocalFileFetcher());
        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        $stateMachine->addToGatheredInformation('SourcePimConfiguration', $sourcePimConfiguration);
    }
}
