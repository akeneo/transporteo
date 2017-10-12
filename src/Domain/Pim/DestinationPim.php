<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimDetectionException;

/**
 * Destination Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPim extends AbstractPim implements Pim
{
    public static function fromDestinationPimConfiguration(
        PimConnection $pimConnection,
        PimConfiguration $destinationPimConfiguration,
        PimApiParameters $apiParameters
    ): DestinationPim {
        $composerJsonRepositoryName = $destinationPimConfiguration->getComposerJson()->getRepositoryName();

        if (!(self::PIM_COMMUNITY_STANDARD === $composerJsonRepositoryName || self::PIM_ENTERPRISE_STANDARD === $composerJsonRepositoryName)) {
            throw new DestinationPimDetectionException(
                sprintf(
                    'Your destination PIM name should be either %s or either %s, currently %s',
                    self::PIM_COMMUNITY_STANDARD,
                    self::PIM_ENTERPRISE_STANDARD,
                    $composerJsonRepositoryName
                )
            );
        }

        $isEnterpriseEdition = false;

        if (self::PIM_ENTERPRISE_STANDARD === $composerJsonRepositoryName) {
            $isEnterpriseEdition = true;
        }

        $dependencies = $destinationPimConfiguration->getComposerJson()->getDependencies();

        $pimVersion = $dependencies->get(self::PIM_COMMUNITY_DEV);

        $matches = [];
        preg_match('/^[^0-9]*([0-9]+\.[0-9]+)/', $pimVersion, $matches);

        if (!isset($matches[1]) || strpos(self::getPimVersionAllowed(), $matches[1]) === false) {
            throw new DestinationPimDetectionException(
                'Your destination PIM version should be '.self::getPimVersionAllowed().' currently : '.$pimVersion
            );
        }

        $databaseHost = $destinationPimConfiguration->getParametersYml()->getDatabaseHost();
        $databasePort = $destinationPimConfiguration->getParametersYml()->getDatabasePort();
        $databaseUser = $destinationPimConfiguration->getParametersYml()->getDatabaseUser();
        $databasePassword = $destinationPimConfiguration->getParametersYml()->getDatabasePassword();
        $databaseName = $destinationPimConfiguration->getParametersYml()->getDatabaseName();

        $realPath = realpath(str_replace(
            DIRECTORY_SEPARATOR.'composer.json',
            '',
            $destinationPimConfiguration->getComposerJson()->getPath()
        ));

        return new self(
            $databaseHost,
            $databasePort,
            $databaseName,
            $databaseUser,
            $databasePassword,
            $isEnterpriseEdition,
            $realPath,
            $pimConnection,
            $apiParameters
        );
    }

    protected static function getPimVersionAllowed(): string
    {
        return '2.0.x';
    }
}
