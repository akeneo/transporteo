<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Pim\DestinationPimConnected;
use Akeneo\PimMigration\Domain\Pim\DestinationPimConnectionAware;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Pim\SourcePimConnected;
use Akeneo\PimMigration\Domain\Pim\SourcePimConnectionAware;

/**
 * Console helper which known where are located the pims to execute command on them.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ConsoleHelper implements SourcePimConnectionAware, DestinationPimConnectionAware
{
    use SourcePimConnected;
    use DestinationPimConnected;

    /** @var Console[] */
    private $consoles = [];

    public function execute(Pim $pim, UnixCommand $command): UnixCommandResult
    {
        $connection = $pim instanceof SourcePim ? $this->sourcePimConnection : $this->destinationPimConnection;

        return $this->get($connection)->execute($command, $pim, $connection);
    }

    public function addConsole(Console $console): void
    {
        $this->consoles[] = $console;
    }

    /**
     * @throws \InvalidArgumentException when th
     */
    protected function get(PimConnection $connection): Console
    {
        foreach ($this->consoles as $console) {
            if ($console->supports($connection)) {
                return $console;
            }
        }

        throw new \InvalidArgumentException('The connection is not supported by any consoles');
    }
}
