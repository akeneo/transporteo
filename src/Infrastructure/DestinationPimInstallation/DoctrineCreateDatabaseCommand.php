<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Infrastructure\Command\Command;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Create the database.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DoctrineCreateDatabaseCommand implements Command
{
    public function getCommand(): string
    {
        return sprintf('%s bin/console doctrine:database:create', (new PhpExecutableFinder())->find());
    }
}
