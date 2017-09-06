<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\UnixCommand;
use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Akeneo\PimMigration\Domain\Pim\AbstractPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;

/**
 * Console usable through docker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DockerConsole extends AbstractConsole implements Console
{
    public function execute(UnixCommand $command, AbstractPim $pim, PimConnection $connection): UnixCommandResult
    {
        // TODO: Implement execute() method.
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof DockerConnection;
    }

    protected function getPrefixPath(Pim $pim): string
    {
        // TODO: Implement getPrefixPath() method.
    }
}
