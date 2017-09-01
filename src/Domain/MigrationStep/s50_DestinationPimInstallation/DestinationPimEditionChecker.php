<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;

/**
 * Check if source PIM and destination PIM are from the same edition.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimEditionChecker
{
    public function check(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $anEnterpriseEdition = 'an Enterprise Edition';
        $notAnEnterpriseEdition = 'not an Enterprise Edition';

        if ($sourcePim->isEnterpriseEdition() !== $destinationPim->isEnterpriseEdition()) {
            throw new DestinationPimCheckConfigurationException(
                sprintf(
                    'The source PIM is %s whereas the destination PIM is %s',
                    $sourcePim->isEnterpriseEdition() ? $anEnterpriseEdition : $notAnEnterpriseEdition,
                    $destinationPim->isEnterpriseEdition() ? $anEnterpriseEdition : $notAnEnterpriseEdition
                )
            );
        }
    }
}
