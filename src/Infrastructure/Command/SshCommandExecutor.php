<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\Command\UnixCommandResult;

/**
 * Run a unix command on a remote server.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshCommandExecutor implements CommandExecutor
{
    /**
     * @throws \Exception
     */
    public function execute(string $command, ?string $path, bool $activateTty): UnixCommandResult
    {
        throw new \Exception('Not Implemented');
    }
}
