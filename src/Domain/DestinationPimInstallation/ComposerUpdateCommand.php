<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command;

/**
 * Composer install command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ComposerUpdateCommand implements Command
{
    public function getCommand(): string
    {
        return 'composer update';
    }
}
