<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Akeneo\PimMigration\Domain\Command\UnsuccessfulCommandException;

/**
 * Execute a Unix Command somewhere.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface CommandExecutor
{
    /**
     * @throws UnsuccessfulCommandException
     */
    public function execute(string $command, ?string $path, bool $activateTty): UnixCommandResult;
}
