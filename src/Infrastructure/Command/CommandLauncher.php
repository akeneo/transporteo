<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Define public contract for a command launcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface CommandLauncher
{
    /**
     * @throws UnsuccessfulCommandException
     */
    public function runCommand(Command $command, ?string $path): void;
}
