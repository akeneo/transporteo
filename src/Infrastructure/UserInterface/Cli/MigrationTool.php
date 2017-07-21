<?php

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

final class MigrationTool extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('akeneo-pim:migrate')
            ->setDescription('Migrate your PIM standard to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Akeneo Pim Migration tool');

        //StepTwo
        $output->writeln('Source Pim Configuration: Collect your configuration files');
        $pimServerInformation = new PimServerInformation(
            ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(),
            'nanou-project'
        );

        $sourcePimConfigurator = new SourcePimConfigurator(new LocalFileFetcher());
        $sourcePimConfiguration = $sourcePimConfigurator->configure($pimServerInformation);

        //StepThree
        $output->writeln('Source Pim Detection: Detect you source pim');
        $sourcePim = SourcePim::fromSourcePimConfiguration($sourcePimConfiguration);

        $output->writeln(sprintf(
            'You want to migrate from an edition %s with %s storage%s',
            $sourcePim->isEnterpriseEdition() ? "Enterprise" : "Community",
            null === $sourcePim->getMongoDatabase() ? "ORM" : "Hybrid",
            $sourcePim->hasIvb() ? "with InnerVariationBundle." : "."
            ));

        //Step four
        if ($sourcePim->isEnterpriseEdition()) {
            $output->writeln('EnterpriseEdition Access Verification');
            $sshVerificator = new SshEnterpriseEditionAccessVerificator();

            $sshVerificator->verify($sourcePim, $sourcePimConfiguration->getSshKey());
            $output->writeln('Access to the Enterprise Edition allowed');
        }


        //State machine
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder
            ->addPlaces(['start', 'configured', 'detected', 'end'])
            ->addTransition(new Transition('to_configure', 'start', 'configured'))
            ->addTransition(new Transition('to_detect', 'configured', 'detected'))
            ->addTransition(new Transition('to_finish', 'detected', 'end'))
            ->build();

        $markingStore = new SingleStateMarkingStore('currentState');
        $workflow = new StateMachine($definition, $markingStore);
    }
}
