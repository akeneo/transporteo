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
    private const PIM_ENTERPRISE_STANDARD = 'akeneo/pim-enterprise-standard';
    private const PIM_COMMUNITY_STANDARD = 'akeneo/pim-community-standard';
    private const PIM_COMMUNITY_DEV = 'akeneo/pim-community-dev';
    private const PIM_VERSION_ALLOWED = '1.7.';
    private const INNER_VARIATION_BUNDLE = 'akeneo/inner-variation-bundle';

    public function detect(SourcePimConfiguration $sourcePimConfiguration): SourcePim
    {
        $composerJsonRepositoryName = $sourcePimConfiguration->getComposerJson()->getRepositoryName();

        if (!(self::PIM_COMMUNITY_STANDARD === $composerJsonRepositoryName || self::PIM_ENTERPRISE_STANDARD === $composerJsonRepositoryName)) {
            throw new SourcePimDetectionException(
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

        $pimVersion = $dependencies->get(self::PIM_COMMUNITY_DEV);

        if (strpos($pimVersion, self::PIM_VERSION_ALLOWED) === false) {
            throw new SourcePimDetectionException('Your PIM version should be '.self::PIM_VERSION_ALLOWED);
        }

        $hasIvb = $dependencies->hasKey(self::INNER_VARIATION_BUNDLE);

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
