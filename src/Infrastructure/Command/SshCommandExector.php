<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Run a unix command on a remote server.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshCommandExector implements CommandExecutor
{
    public function execute(string $command, ?string $path): void
    {
        // TODO: Implement execute() method.
    }
}
