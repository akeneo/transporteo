<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Check if the destination PIM is ready to use.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimConfigurationChecker
{
    /** @var DestinationPimEditionChecker */
    private $destinationPimEditionChecker;

    /** @var DestinationPimSystemRequirementsChecker */
    private $destinationPimSystemRequirementsChecker;

    /** @var DestinationPimVersionChecker */
    private $destinationPimVersionChecker;

    public function __construct(
        DestinationPimEditionChecker $destinationPimEditionChecker,
        DestinationPimSystemRequirementsChecker $destinationPimSystemRequirementsChecker,
        DestinationPimVersionChecker $destinationPimVersionChecker
    ) {
        $this->destinationPimEditionChecker = $destinationPimEditionChecker;
        $this->destinationPimSystemRequirementsChecker = $destinationPimSystemRequirementsChecker;
        $this->destinationPimVersionChecker = $destinationPimVersionChecker;
    }

    public function check(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->destinationPimEditionChecker->check($sourcePim, $destinationPim);
        $this->destinationPimVersionChecker->check($destinationPim);
        $this->destinationPimSystemRequirementsChecker->check($destinationPim);
    }
}
