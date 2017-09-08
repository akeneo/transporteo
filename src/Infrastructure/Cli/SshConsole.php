<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;

/**
 * Console working through SSH.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshConsole extends AbstractConsole implements Console
{
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
        $connection = $pim->getConnection();

        if ($command instanceof MySqlQueryCommand) {
            $query = sprintf(
                '%s -s -e "%s;"',
                $this->getMySqlConnectionChain($pim),
                $command->getCommand()
            );

            $output = '';

            $lines = array_filter(explode(PHP_EOL, $output), function ($element) {
                return !empty(trim($element));
            });

            $results = [];

            $columns = str_getcsv(array_shift($lines), "\t");

            foreach ($lines as $line) {
                $cells = str_getcsv($line, "\t");
                $results[] = array_combine($columns, $cells);
            }

            return new CommandResult(1, $results);
        }

        return new CommandResult(1, '');
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
