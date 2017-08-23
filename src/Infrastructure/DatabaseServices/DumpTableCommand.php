<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Domain\Command\Command;

/**
 * Dump a table into an sql file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DumpTableCommand implements Command
{
    /** @var AbstractPim */
    private $pim;

    /** @var string */
    private $tableName;

    /** @var string */
    private $dumpLocation;

    public function __construct(AbstractPim $pim, string $tableName, string $dumpLocation)
    {
        $this->pim = $pim;
        $this->tableName = $tableName;
        $this->dumpLocation = $dumpLocation;
    }

    public function getCommand(): string
    {
        return sprintf(
            'mysqldump -u %s -p%s %s %s > %s',
            $this->pim->getDatabaseUser(),
            $this->pim->getDatabasePassword(),
            $this->pim->getDatabaseName(),
            $this->tableName,
            $this->dumpLocation
        );
    }
}
