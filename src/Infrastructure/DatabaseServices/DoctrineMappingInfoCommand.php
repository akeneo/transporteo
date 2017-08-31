<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Infrastructure\Command\Command;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Command to check the entity mapping between database and doctrine.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DoctrineMappingInfoCommand implements Command
{
    public function getCommand(): string
    {
        return sprintf('%s bin/console doctrine:mapping:info', (new PhpExecutableFinder())->find());
    }
}
