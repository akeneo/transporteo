<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Representation of what a console is able to execute.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface UnixCommand
{
    public function getCommand(): string;
}
