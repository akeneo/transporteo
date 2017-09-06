<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Command\AbstractUnixCommand;
use Akeneo\PimMigration\Domain\Command\UnixCommand;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlDumpCommand extends AbstractUnixCommand implements UnixCommand
{
    public function __construct(string $tableName, string $path)
    {
        parent::__construct(
            sprintf(
                '%s > %s',
                $tableName,
                $path
            )
        );
    }
}
