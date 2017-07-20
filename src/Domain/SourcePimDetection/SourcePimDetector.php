<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimDetection;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfiguration;

/**
 * Aims to create a SourcePim representation.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimDetector
{
    const PIM_ENTERPRISE_STANDARD = 'akeneo/pim-enterprise-standard';
    const PIM_COMMUNITY_STANDARD = 'akeneo/pim-community-standard';

    public function detect(SourcePimConfiguration $sourcePimConfiguration): SourcePim
    {
        $composerJsonRepositoryName = $sourcePimConfiguration->getComposerJson()->getRepositoryName();

        if (!(self::PIM_COMMUNITY_STANDARD === $composerJsonRepositoryName || self::PIM_ENTERPRISE_STANDARD === $composerJsonRepositoryName)) {
            throw new NotAcceptablePimException(
                sprintf(
                    'Your PIM name should be either %s or either %s, currently %s',
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

        $dependencies = $sourcePimConfiguration->getComposerJson()->getDependencies();

        $pimVersion = $dependencies->get('akeneo/pim-community-dev');

        if (strpos($pimVersion, '1.7.') === false) {
            throw new NotAcceptablePimException('Your PIM version should be 1.7');
        }

        $hasIvb = $dependencies->hasKey('akeneo/inner-variation-bundle');

        $mongoDbInformation = $sourcePimConfiguration->getPimParameters()->getMongoDbInformation();
        $mongoDbDatabase = $sourcePimConfiguration->getPimParameters()->getMongoDbDatabase();
        $databaseHost = $sourcePimConfiguration->getParametersYml()->getDatabaseHost();
        $databasePort = $sourcePimConfiguration->getParametersYml()->getDatabasePort();
        $databaseUser = $sourcePimConfiguration->getParametersYml()->getDatabaseUser();
        $databasePassword = $sourcePimConfiguration->getParametersYml()->getDatabasePassword();
        $databaseName = $sourcePimConfiguration->getParametersYml()->getDatabaseName();

        $enterpriseRepository = null;

        if ($isEnterpriseEdition) {
            $enterpriseRepository = $sourcePimConfiguration
                ->getComposerJson()
                ->getRepositories()
                ->filter(function ($element) {
                    return false !== strpos($element['url'], 'enterprise');
                })
                ->first()['url'];
        }

        return new SourcePim(
            $databaseHost,
            $databasePort,
            $databaseName,
            $databaseUser,
            $databasePassword,
            $mongoDbInformation,
            $mongoDbDatabase,
            $isEnterpriseEdition,
            $enterpriseRepository,
            $hasIvb
        );
    }
}
