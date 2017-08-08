<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

/**
 * Define a command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface Command
{
    public function getCommand(): string;
}
