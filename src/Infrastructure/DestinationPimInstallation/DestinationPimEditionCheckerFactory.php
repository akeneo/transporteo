<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimEditionChecker;

/**
 * Factory for edition PIM checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimEditionCheckerFactory
{
    public function createDestinationPimEditionChecker(): DestinationPimEditionChecker
    {
        return new DestinationPimEditionChecker();
    }
}
