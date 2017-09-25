<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\ApiCommand;
use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Command\UnsuccessfulCommandException;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Run a SymfonyProcess locally.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalConsole extends AbstractConsole implements Console
{
    /** @var LocalMySqlQueryExecutor */
    private $localMySqlQueryExecutor;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LocalMySqlQueryExecutor $localMySqlQueryExecutor, ApiCommandExecutor $apiCommandExecutor, LoggerInterface $logger)
    {
        $this->localMySqlQueryExecutor = $localMySqlQueryExecutor;
        $this->logger = $logger;

        parent::__construct($apiCommandExecutor);
    }

    public function execute(Command $command, Pim $pim): CommandResult
    {
        $this->logger->debug(sprintf('LocalConsole: executing %s command -> %s', get_class($command), $command->getCommand()));

        if ($command instanceof MySqlExecuteCommand) {
            $this->localMySqlQueryExecutor->execute($command->getCommand(), $pim);

            return new CommandResult(1, '');
        }

        if ($command instanceof MySqlQueryCommand) {
            return new CommandResult(1, $this->localMySqlQueryExecutor->query($command->getCommand(), $pim));
        }

        if ($command instanceof ApiCommand) {
            return $this->apiCommandExecutor->execute($command, $pim);
        }

        $commandToLaunch = $this->getProcessedCommand($command, $pim);
        $process = new Process($commandToLaunch, '');

        $process->enableOutput();
        $process->setTimeout(2 * 3600);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $authorizedExitCodes = [
                129, // Hangup
                130, // Interrupt
            ];
            if (!in_array($e->getProcess()->getExitCode(), $authorizedExitCodes)) {
                throw new UnsuccessfulCommandException($process->getErrorOutput(), $e->getCode(), $e);
            }
        }

        return new CommandResult($process->getExitCode(), $process->getOutput());
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof Localhost;
    }
}
