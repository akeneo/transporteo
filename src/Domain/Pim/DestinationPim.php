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

        if (strpos($pimVersion, self::getPimVersionAllowed()) === false) {
            throw new DestinationPimDetectionException(
                'Your destination PIM version should be '.self::getPimVersionAllowed().' currently : '.$pimVersion
            );
        }

        $databaseHost = $destinationPimConfiguration->getParametersYml()->getDatabaseHost();
        $databasePort = $destinationPimConfiguration->getParametersYml()->getDatabasePort();
        $databaseUser = $destinationPimConfiguration->getParametersYml()->getDatabaseUser();
        $databasePassword = $destinationPimConfiguration->getParametersYml()->getDatabasePassword();
        $databaseName = $destinationPimConfiguration->getParametersYml()->getDatabaseName();

        $enterpriseRepository = null;

        if ($isEnterpriseEdition) {
            $enterpriseRepository = $destinationPimConfiguration
                ->getComposerJson()
                ->getRepositories()
                ->filter(function ($element) {
                    return false !== strpos($element['url'], 'enterprise');
                })
                ->first()['url'];
        }

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
            $enterpriseRepository,
            $realPath,
            $pimConnection,
            $apiParameters
        );
    }

    protected static function getPimVersionAllowed(): string
    {
        //TODO PUT 2.0
        return '1.8.x-dev@dev';
    }
}
