<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Command\AbstractUnixCommand;
use Akeneo\PimMigration\Domain\Command\UnixCommand;

/**
 * Type to define a query, only a query should be used for this class.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlQueryCommand extends AbstractUnixCommand implements UnixCommand
{
    public function __construct(string $query)
    {
        parent::__construct($query);
    }
}
