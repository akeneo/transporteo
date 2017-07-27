<?php

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MigrationTool extends Command
{
    /** @var ContainerBuilder */
    private $container;

    public function __construct(ContainerBuilder $container, $name = null)
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

        $cliQuestionAsker = new ConsolePrinterAndAsker($input, $output, $this->getHelper('question'));

        $stateMachineSubscribers = $this->container->findTaggedServiceIds('migration_tool.subscriber');

        foreach ($stateMachineSubscribers as $serviceId => $values) {
            $service = $this->container->get($serviceId);
            $service->setPrinterAndAsker($cliQuestionAsker);
        }

        $stateMachine->start();

        $output->writeln('Migration finished');
    }
}
