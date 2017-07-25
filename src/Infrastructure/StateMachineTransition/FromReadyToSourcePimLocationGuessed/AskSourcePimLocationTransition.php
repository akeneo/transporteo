<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromReadyToSourcePimLocationGuessed;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * Ask for the location of the Source Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AskSourcePimLocationTransition implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.ask_source_pim_location' => 'askSourcePimLocation',
        ];
    }

    /**
     * @param Event $event
     */
    public function askSourcePimLocation(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();
        $input = $stateMachine->getGatheredInformation(InputInterface::class);
        $output = $stateMachine->getGatheredInformation(OutputInterface::class);
        $helper = $stateMachine->getGatheredInformation(QuestionHelper::class);

        $projectNameQuestion = new Question('What is the name of the project you want to migrate? ');
        $projectName = $helper->ask($input, $output, $projectNameQuestion);

        $pimLocationQuestion = new ChoiceQuestion('Where is located your PIM? ', ['local', 'server']);
        $pimLocation = $helper->ask($input, $output, $pimLocationQuestion);

        $stateMachine->addToGatheredInformation('ProjectName', $projectName);
        $stateMachine->addToGatheredInformation('PimSourceLocation', $pimLocation);
    }
}
