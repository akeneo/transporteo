<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Console helper which known where are located the pims to execute command on them.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ConsoleHelper
{
    /** @var Console[] */
    private $consoles = [];

    public function execute(Pim $pim, Command $command): CommandResult
    {
        return $this->get($pim->getConnection())->execute($command, $pim, $pim->getConnection());
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
