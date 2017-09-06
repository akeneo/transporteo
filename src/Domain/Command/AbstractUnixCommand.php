<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Abstract unix command taking command as it is.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractUnixCommand implements UnixCommand
{
    /** @var string */
    private $command;

    public function __construct(string $tableName)
    {
        $this->command = $tableName;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
