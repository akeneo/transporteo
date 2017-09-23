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
                '%s %s',
                $this->getMySqlConnectionChain($pim),
                $command->getCommand()
            );
        }

        if ($command instanceof MySqlExportTableCommand) {
            return sprintf(
                'mysqldump --port=%s -u%s -p%s %s %s',
                strval($pim->getMysqlPort()),
                $pim->getDatabaseUser(),
                $pim->getDatabasePassword(),
                $pim->getDatabaseName(),
                $command->getCommand()
            );
        }

        throw new \InvalidArgumentException(sprintf('Not supported command of class %s'.get_class($command)));
    }

    protected function getMySqlConnectionChain(Pim $pim): string
    {
        return sprintf(
            'mysql --host=%s --port=%s -u%s -p%s %s',
            $pim->getMysqlHost(),
            strval($pim->getMysqlPort()),
            $pim->getDatabaseUser(),
            $pim->getDatabasePassword(),
            $pim->getDatabaseName()
        );
    }
}
