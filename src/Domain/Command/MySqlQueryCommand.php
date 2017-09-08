<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Type to define a query, only a query should be used for this class.
 * A query compared to an execution is something that returns a result, it's a SELECT clauses for example.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlQueryCommand extends AbstractCommand implements Command
{
    public function __construct(string $query)
    {
        parent::__construct($query);
    }
}
