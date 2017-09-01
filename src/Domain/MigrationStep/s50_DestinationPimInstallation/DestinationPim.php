<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\AbstractPim;

/**
 * Destination Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPim extends AbstractPim
{
    /** @var string */
    protected $path;

    /** @var string */
    protected $indexName;

    /** @var string */
    protected $indexHosts;

    public function __construct(
        string $mysqlHost = 'mysql',
        int $mysqlPort = 3306,
        string $databaseName = 'akeneo_pim',
        string $databaseUser = 'akeneo_pim',
        string $databasePassword = 'akeneo_pim',
        bool $isEnterpriseEdition,
        ?string $enterpriseRepository,
        string $indexName = 'akeneo_pim',
        string $indexHosts,
        string $path
    ) {
        parent::__construct(
            $mysqlHost,
            $mysqlPort,
            $databaseName,
            $databaseUser,
            $databasePassword,
            $isEnterpriseEdition,
            $enterpriseRepository,
            $path
        );

        $this->indexName = $indexName;
        $this->indexHosts = $indexHosts;
    }

    public static function fromDestinationPimConfiguration(PimConfiguration $destinationPimConfiguration): DestinationPim
    {
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
        $indexHosts = $destinationPimConfiguration->getParametersYml()->getIndexHosts();

        if (null === $indexHosts) {
            throw new DestinationPimDetectionException('Your configuration should have an index_hosts key in your parameters.yml file');
        }

        $indexName = $destinationPimConfiguration->getParametersYml()->getIndexName();

        if (null === $indexName) {
            throw new DestinationPimDetectionException('Your configuration should have an index_name key in your parameters.yml file');
        }

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

        return new self(
            $databaseHost,
            $databasePort,
            $databaseName,
            $databaseUser,
            $databasePassword,
            $isEnterpriseEdition,
            $enterpriseRepository,
            $indexName,
            $indexHosts,
            realpath(str_replace(
                DIRECTORY_SEPARATOR.'composer.json',
                '',
                $destinationPimConfiguration->getComposerJson()->getPath()
            ))
        );
    }

    protected static function getPimVersionAllowed(): string
    {
        //TODO PUT 2.0
        return '1.8.x-dev@dev';
    }
}
