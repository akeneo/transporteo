<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Command\AbstractUnixCommand;
use Akeneo\PimMigration\Domain\Command\UnixCommand;

/**
 * MySqlCommand executed as it is at the end of a mysql connection.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MysqlRawCommand extends AbstractUnixCommand implements UnixCommand
{
}
