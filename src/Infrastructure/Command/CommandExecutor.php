<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

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
    public function execute(string $command, ?string $path): void;
}
