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

    public function __construct(DestinationPimEditionChecker $destinationPimEditionChecker, DestinationPimSystemRequirementsChecker $destinationPimSystemRequirementsChecker)
    {
        $this->destinationPimEditionChecker = $destinationPimEditionChecker;
        $this->destinationPimSystemRequirementsChecker = $destinationPimSystemRequirementsChecker;
    }

    public function check(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $this->destinationPimEditionChecker->check($sourcePim, $destinationPim);
            $this->destinationPimSystemRequirementsChecker->check($destinationPim);
        } catch (\Exception $e) {
            throw new DestinationPimCheckConfigurationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
