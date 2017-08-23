<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\ExtraDataMigration;

use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ShowTablesCommand implements Command
{
    /** @var AbstractPim */
    private $pim;

    public function __construct(AbstractPim $pim)
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
