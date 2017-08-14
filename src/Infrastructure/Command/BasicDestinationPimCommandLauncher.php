<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Able to launch command directly on the host.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BasicDestinationPimCommandLauncher extends AbstractDestinationPimCommandLauncher implements DestinationPimCommandLauncher
{
    protected function getStringCommand(Command $command): string
    {
        return escapeshellcmd($command->getCommand());
    }
}