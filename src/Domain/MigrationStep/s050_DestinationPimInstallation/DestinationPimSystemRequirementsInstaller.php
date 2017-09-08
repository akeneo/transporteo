<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Install all system requirements (e.g: mysql / elasticsearch / folders / database).
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface DestinationPimSystemRequirementsInstaller
{
    public function install(DestinationPim $destinationPim): void;

    public function supports(PimConnection $connection): bool;
}
