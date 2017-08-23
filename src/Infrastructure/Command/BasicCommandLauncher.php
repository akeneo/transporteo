<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Command\CommandLauncher;

/**
 * Able to launch command directly on the host.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BasicCommandLauncher extends AbstractCommandLauncher implements CommandLauncher
{
    protected function getStringCommand(Command $command): string
    {
        return $command->getCommand();
    }
}
