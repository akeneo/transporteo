<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Command\MySqlExportTableCommand;
use Akeneo\PimMigration\Domain\Command\MySqlImportTableCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;

/**
 * Abstraction of a console.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractConsole implements Console
{
    /** @var ApiCommandExecutor */
    protected $apiCommandExecutor;

    public function __construct(ApiCommandExecutor $apiCommandExecutor)
    {
        $this->apiCommandExecutor = $apiCommandExecutor;
    }

    protected function getProcessedCommand(Command $command, Pim $pim): ?string
    {
        if ($command instanceof SymfonyCommand) {
            if ($pim instanceof DestinationPim) {
                return sprintf('%sbin/console %s', $pim->absolutePath().DIRECTORY_SEPARATOR, $command->getCommand());
            }

            return sprintf('%sapp/console %s', $pim->absolutePath().DIRECTORY_SEPARATOR, $command->getCommand());
        }

        if ($command instanceof MySqlImportTableCommand) {
            return sprintf(
                'mysql %s %s',
                $this->getMySqlConnectionChain($pim),
                $command->getCommand()
            );
        }

        if ($command instanceof MySqlExportTableCommand) {
            return sprintf(
                'mysqldump %s %s',
                $this->getMySqlConnectionChain($pim),
                $command->getCommand()
            );
        }

        throw new \InvalidArgumentException(sprintf('Not supported command of class %s'.get_class($command)));
    }

    protected function getMySqlConnectionChain(Pim $pim): string
    {
        $connectionChain = '';

        $host = $pim->getMysqlHost();
        if (!empty($host) && 'localhost' !== $host) {
            $connectionChain .= sprintf('--host=%s ', $host);
        }

        $port = $pim->getMysqlPort();
        if (!empty($port)) {
            $connectionChain .= sprintf('--port=%s ', $port);
        }

        $connectionChain .= sprintf('-u%s ', $pim->getDatabaseUser());

        $password = $pim->getDatabasePassword();
        if (!empty($password)) {
            $connectionChain .= sprintf('-p%s ', $password);
        }

        $connectionChain .= $pim->getDatabaseName();

        return $connectionChain;
    }
}
