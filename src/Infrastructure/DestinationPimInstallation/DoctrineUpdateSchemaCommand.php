<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\Command;

/**
 * Update doctrine schema.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DoctrineUpdateSchemaCommand implements Command
{
    public function getCommand(): string
    {
        return 'php bin/console doctrine:schema:update --force';
    }
}
