<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimDetection;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfiguration;

/**
 * Class to represent the source PIM state.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePim
{
    /** @var string */
    private $mysqlHost;

    /** @var int */
    private $mysqlPort;

    /** @var string */
    private $databaseName;

    /** @var string */
    private $databaseUser;

    /** @var string */
    private $databasePassword;

    /** @var null|string */
    private $mongoDbInformation;

    /** @var null|string */
    private $mongoDatabase;

    /** @var bool */
    private $isEnterpriseEdition;

    /** @var null|string */
    private $enterpriseRepository;

    /** @var bool */
    private $hasIvb;

    private const PIM_ENTERPRISE_STANDARD = 'akeneo/pim-enterprise-standard';
    private const PIM_COMMUNITY_STANDARD = 'akeneo/pim-community-standard';
    private const PIM_COMMUNITY_DEV = 'akeneo/pim-community-dev';
    private const PIM_VERSION_ALLOWED = '1.7.';
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
        bool $hasIvb
    ) {
        $this->mysqlHost = $mysqlHost;
        $this->mysqlPort = $mysqlPort;
        $this->databaseName = $databaseName;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        $this->mongoDbInformation = $mongoDbInformation;
        $this->mongoDatabase = $mongoDatabase;
        $this->isEnterpriseEdition = $isEnterpriseEdition;
        $this->enterpriseRepository = $enterpriseRepository;
        $this->hasIvb = $hasIvb;
    }

    public static function fromSourcePimConfiguration(SourcePimConfiguration $sourcePimConfiguration): SourcePim
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
            $hasIvb
        );
    }

    public function getMysqlHost(): string
    {
        return $this->mysqlHost;
    }

    public function getMysqlPort(): int
    {
        return $this->mysqlPort;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    public function getDatabaseUser(): string
    {
        return $this->databaseUser;
    }

    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }

    public function getMongoDbInformation(): ?string
    {
        return $this->mongoDbInformation;
    }

    public function getMongoDatabase(): ?string
    {
        return $this->mongoDatabase;
    }

    public function isEnterpriseEdition(): bool
    {
        return $this->isEnterpriseEdition;
    }

    public function getEnterpriseRepository(): ?string
    {
        return $this->enterpriseRepository;
    }

    public function hasIvb(): bool
    {
        return $this->hasIvb;
    }
}
