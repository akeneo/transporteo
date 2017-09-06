<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\DestinationPimConnected;
use Akeneo\PimMigration\Domain\Pim\DestinationPimConnectionAware;

/**
 * Helper which known where are located the pims to give the right installer.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimSystemRequirementsInstallerHelper implements DestinationPimConnectionAware
{
    use DestinationPimConnected;

    /** @var DestinationPimSystemRequirementsInstaller[] */
    private $destinationPimSystemRequirementsInstallers = [];

    public function install(DestinationPim $pim): void
    {
        $this->get()->install($pim);
    }

    protected function get(): DestinationPimSystemRequirementsInstaller
    {
        foreach ($this->destinationPimSystemRequirementsInstallers as $destinationPimSystemRequirementsInstaller) {
            if ($destinationPimSystemRequirementsInstaller->supports($this->destinationPimConnection)) {
                return $destinationPimSystemRequirementsInstaller;
            }
        }

        throw new \InvalidArgumentException('The connection is not supported by any destinationPimSystemRequirementsInstaller');
    }

    public function addDestinationPimSystemRequirementsInstaller(
        DestinationPimSystemRequirementsInstaller $destinationPimSystemRequirementsInstaller
    ): void {
        $this->destinationPimSystemRequirementsInstallers[] = $destinationPimSystemRequirementsInstaller;
    }
}
