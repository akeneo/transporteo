<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\ParametersYmlGenerator;

/**
 * Create PreConfigurator for a destination pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimParametersYmlGeneratorFactory
{
    public function createDestinationPimParametersYmlGenerator(string $destinationPath): ParametersYmlGenerator
    {
        return new ParametersYmlGenerator($destinationPath);
    }
}
