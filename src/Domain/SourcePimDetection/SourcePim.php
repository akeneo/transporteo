<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimDetection;

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
