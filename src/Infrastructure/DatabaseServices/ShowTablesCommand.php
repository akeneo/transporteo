<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Infrastructure\Command\Command;

/**
 * Command to get tables name of a PIM project.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ShowTablesCommand implements Command
{
    /** @var Pim */
    private $pim;

    public function __construct(Pim $pim)
    {
        $this->pim = $pim;
    }

    public function getCommand(): string
    {
        return sprintf(
            'mysql -P%s -h%s -u%s -p%s -e "SHOW TABLES;" %s -s',
            (string) $this->pim->getMysqlPort(),
            $this->pim->getMysqlHost(),
            $this->pim->getDatabaseUser(),
            $this->pim->getDatabasePassword(),
            $this->pim->getDatabaseName()
        );
    }
}
