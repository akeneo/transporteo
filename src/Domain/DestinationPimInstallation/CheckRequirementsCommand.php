<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command;

/**
 * Check Pim Requirements command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CheckRequirementsCommand implements Command
{
    public function getCommand(): string
    {
        return 'php bin/console pim:installer:check-requirements';
    }
}