<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Infrastructure\Command\Command;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ImportDockerDumpCommand implements Command
{
    /** @var AbstractPim */
    private $pim;

    /** @var string */
    private $dumpLocation;

    public function __construct(AbstractPim $pim, string $dumpLocation)
    {
        $this->pim = $pim;
        $this->dumpLocation = $dumpLocation;
    }

    public function getCommand(): string
    {
        return sprintf(
            'docker exec -i mysql_destination mysql -u %s -p%s %s < %s',
            $this->pim->getDatabaseUser(),
            $this->pim->getDatabasePassword(),
            $this->pim->getDatabaseName(),
            $this->dumpLocation
        );
    }
}
