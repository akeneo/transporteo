<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;

/**
 * Allow to dump the state machine in a dot file..
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class StateMachineDumper extends Command
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
            ->setName('state-machine:dump')
            ->setDescription('Dump the state machine into a dot file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $dumper = new GraphvizDumper();

        $stateMachine = $this->container->get('state_machine.migration_tool');

        $content = $dumper->dump(
            $stateMachine->getDefinition(),
            null,
            [
                'graph' => ['ratio' => 'fill', 'rankdir' => 'TB'],
                'node' => ['fontsize' => 12, 'width' => '2.3'],
            ]);

        $fs = new Filesystem();

        $fs->dumpFile('stateMachineMigrationTool.dot', $content);

        $output->writeln('You now have to run "dot -Tpng stateMachineMigrationTool.dot -o stateMachineMigrationTool.png"');
    }
}
