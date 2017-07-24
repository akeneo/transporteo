<?php

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshFileFetcher;
use Akeneo\PimMigration\Infrastructure\SshKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

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

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $output->writeln('Source Pim Configuration: Collect your configuration files');

        $projectNameQuestion = new Question('What is the name of the project you want to migrate? ');
        $projectName = $helper->ask($input, $output, $projectNameQuestion);

        $pimLocationQuestion = new ChoiceQuestion('Where is located your PIM? ', ['local', 'server']);

        $pimLocation = $helper->ask($input, $output, $pimLocationQuestion);

        $fetcher = null;
        $composerJsonPathQuestion = null;
        $sshKeySourcePimServer = null;

        if ('server' === $pimLocation) {
            $hostQuestion = new Question('What is the hostname of the source PIM server? ');
            $host = $helper->ask($input, $output, $hostQuestion);

            $portQuestion = new Question('What is the SSH port of the source PIM server? ', 22);
            $port = $helper->ask($input, $output, $portQuestion);

            $userNameQuestion = new Question('What is the SSH user you want to connect with ? ');
            $user = $helper->ask($input, $output, $userNameQuestion);

            $sshKeyPathQuestion = new Question('Where is located the private SSH key able to connect to the server ? ');
            $sshPath = $helper->ask($input, $output, $sshKeyPathQuestion);

            $sshKeySourcePimServer = new SshKey($sshPath);
            $serverAccess = new ServerAccessInformation($host, $port, $user, $sshKeySourcePimServer);
            $fetcher = new SshFileFetcher($serverAccess);

            $composerJsonPathQuestion = new Question('Where is located the composer.json on the server? ');
        } else {
            $fetcher = new LocalFileFetcher();
            $composerJsonPathQuestion = new Question('Where is located the composer.json on your computer? ');
        }

        $composerJsonPath = $helper->ask($input, $output, $composerJsonPathQuestion);
        $pimServerInformation = new PimServerInformation($composerJsonPath, $projectName);

        $sourcePimConfigurator = new SourcePimConfigurator($fetcher);
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

            $sshKeyEnterpriseEditionServer = null;

            if (null !== $sshKeySourcePimServer) {
                $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKeySourcePimServer);
                try {
                    $sshVerificator->verify($sourcePim);
                } catch (EnterpriseEditionAccessException $exception) {
                    $output->writeln('You are not allowed to connect to the EnterpriseEdition server with the SSH key you give us already.');
                }

                $sshKeyPathQuestion = new Question('Where is located your SSH key allowed to connect to Akeneo Enterprise Edition distibution? ');
                $sshPath = $helper->ask($input, $output, $sshKeyPathQuestion);
                $sshKey = new SshKey($sshPath);
                $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);
                $sshVerificator->verify($sourcePim);

            } else {

                $sshKeyPathQuestion = new Question('Where is located your SSH key allowed to connect to Akeneo Enterprise Edition distibution? ');
                $sshPath = $helper->ask($input, $output, $sshKeyPathQuestion);
                $sshKey = new SshKey($sshPath);
                $sshVerificator = new SshEnterpriseEditionAccessVerificator($sshKey);
                $sshVerificator->verify($sourcePim);
            }
            $output->writeln('Access to the Enterprise Edition allowed.');
        }

        $output->writeln('Migration finished');
    }
}
