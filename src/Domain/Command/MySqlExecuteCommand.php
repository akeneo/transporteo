<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Type to define an execution, only a execution should be used for this class.
 * An execution, is something that  will update the database, DELETE / UPDATE / INSERT for examples.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MySqlExecuteCommand extends AbstractCommand implements Command
{
    public function __construct(string $query)
    {
        parent::__construct($query);
    }
}
