<?php

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

final class MigrationTool extends Command
{
    /** @var Container */
    private $container;

    public function __construct(Container $container, $name = null)
    {
        $this->container = $container;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('akeneo-pim:migrate')
            ->setDescription('Migrate your PIM standard to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $stateMachine = new MigrationToolStateMachine($this->container->get('state_machine.migration_tool'));
        $stateMachine->addToGatheredInformation(OutputInterface::class, $output);
        $stateMachine->addToGatheredInformation(InputInterface::class, $input);
        $stateMachine->addToGatheredInformation(QuestionHelper::class, $this->getHelper('question'));

        $stateMachine->start();

        $output->writeln('Migration finished');
    }
}
