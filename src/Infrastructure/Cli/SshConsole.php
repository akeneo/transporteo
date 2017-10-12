<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\ApiCommand;
use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\ImpossibleConnectionException;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Psr\Log\LoggerInterface;

/**
 * Console working through SSH.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshConsole extends AbstractConsole implements Console
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(ApiCommandExecutor $apiCommandExecutor, LoggerInterface $logger)
    {
        parent::__construct($apiCommandExecutor);
        $this->logger = $logger;
    }

    public function getProcessedCommand(Command $command, Pim $pim): string
    {
        $processedCommand = parent::getProcessedCommand($command, $pim);

        if (null != $processedCommand) {
            return $processedCommand;
        }

        throw new \InvalidArgumentException(sprintf('Not supported command of class %s'.get_class($command)));
    }

    public function execute(Command $command, Pim $pim): CommandResult
    {
        if ($command instanceof ApiCommand) {
            $this->logger->debug(sprintf('LocalConsole: executing ApiCommand -> %s', $command->getCommand()));

            return $this->apiCommandExecutor->execute($command, $pim);
        }

        $connection = $pim->getConnection();

        if (!$connection instanceof SshConnection) {
            throw new \InvalidArgumentException('Expected %s, %s given', SshConnection::class, get_class($connection));
        }

        $ssh = new SSH2($connection->getHost(), $connection->getPort());
        $rsa = new RSA();
        $rsa->load($connection->getSshKey()->getKey());

        if (!$ssh->isConnected()) {
            if (!$ssh->login($connection->getUsername(), $rsa)) {
                throw new ImpossibleConnectionException(
                    sprintf(
                        'Impossible to login to %s@%s:%d using this ssh key : %s',
                        $connection->getUsername(),
                        $connection->getHost(),
                        $connection->getPort(),
                        $connection->getSshKey()->getPath()
                    )
                );
            }
        }

        if ($command instanceof MySqlQueryCommand || $command instanceof MySqlExecuteCommand) {
            $query = sprintf('mysql %s -e "%s;"', $this->getMySqlConnectionChain($pim), $command->getCommand());

            $this->logger->debug(sprintf('SshConsole: executing MySqlQueryCommand -> %s', $command->getCommand()));

            $output = $ssh->exec($query);

            $lines = array_filter(explode(PHP_EOL, $output), function ($element) {
                return !empty(trim($element));
            });

            $results = [];

            $columns = str_getcsv(array_shift($lines), "\t");

            foreach ($lines as $line) {
                $cells = str_getcsv($line, "\t");
                $results[] = array_combine($columns, $cells);
            }

            return new CommandResult($ssh->getExitStatus(), $results);
        }

        $processedCommand = $this->getProcessedCommand($command, $pim);

        $this->logger->debug(sprintf('SshConsole: executing %s command -> %s', get_class($command), $processedCommand));

        $output = $ssh->exec($processedCommand);

        return new CommandResult($ssh->getExitStatus(), $output);
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof SshConnection;
    }

    protected function getPrefixPath(Pim $pim): string
    {
        return $pim->absolutePath().DIRECTORY_SEPARATOR;
    }
}
