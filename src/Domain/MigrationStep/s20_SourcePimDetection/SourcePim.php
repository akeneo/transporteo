<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection;

use Akeneo\PimMigration\Domain\Pim\AbstractPim;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;

/**
 * Class to represent the source PIM state.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePim extends AbstractPim
{
    /** @var null|string */
    private $mongoDbInformation;

    /** @var null|string */
    private $mongoDatabase;

    /** @var bool */
    private $hasIvb;

    private const INNER_VARIATION_BUNDLE = 'akeneo/inner-variation-bundle';

    public function __construct(
        string $mysqlHost,
        int $mysqlPort,
        string $databaseName,
        string $databaseUser,
        string $databasePassword,
        ?string $mongoDbInformation,
        ?string $mongoDatabase,
        bool $isEnterpriseEdition,
        ?string $enterpriseRepository,
        bool $hasIvb,
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

        $this->mongoDbInformation = $mongoDbInformation;
        $this->mongoDatabase = $mongoDatabase;
        $this->hasIvb = $hasIvb;
    }

    public static function fromSourcePimConfiguration(string $realPath, PimConfiguration $sourcePimConfiguration): SourcePim
    {
        $composerJsonRepositoryName = $sourcePimConfiguration->getComposerJson()->getRepositoryName();

        if (!(self::PIM_COMMUNITY_STANDARD === $composerJsonRepositoryName || self::PIM_ENTERPRISE_STANDARD === $composerJsonRepositoryName)) {
            throw new SourcePimDetectionException(
                sprintf(
                    'Your PIM distribution should be either "%s" or "%s". It appears you try to migrate a "%s" instead.',
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

        if (strpos($pimVersion, self::getPimVersionAllowed()) === false) {
            throw new SourcePimDetectionException('Your PIM version should be '.self::getPimVersionAllowed());
        }

        $hasIvb = $dependencies->hasKey(self::INNER_VARIATION_BUNDLE);

        $mongoDbInformation = $sourcePimConfiguration->getPimParameters()->getMongoDbInformation() ??
            $sourcePimConfiguration->getParametersYml()->getMongoDbInformation();
        $mongoDbDatabase = $sourcePimConfiguration->getPimParameters()->getMongoDbDatabase() ??
            $sourcePimConfiguration->getParametersYml()->getMongoDbDatabase();
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

        return new self(
            $databaseHost,
            $databasePort,
            $databaseName,
            $databaseUser,
            $databasePassword,
            $mongoDbInformation,
            $mongoDbDatabase,
            $isEnterpriseEdition,
            $enterpriseRepository,
            $hasIvb,
            $realPath
        );
    }

    public function getMongoDbInformation(): ?string
    {
        return $this->mongoDbInformation;
    }

    public function getMongoDatabase(): ?string
    {
        return $this->mongoDatabase;
    }

    public function hasIvb(): bool
    {
        return $this->hasIvb;
    }

    protected static function getPimVersionAllowed(): string
    {
        return  '1.7.';
    }
}
