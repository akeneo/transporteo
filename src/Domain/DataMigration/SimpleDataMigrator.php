<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;

/**
 * Migrate a table without extra process.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SimpleDataMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $tableMigrator;

    /** @var string */
    private $supportedTableName;

    /** @var LoggerInterface */
    private $logger;

    /** @var bool */
    private $isEeOnly;

    public function __construct(TableMigrator $tableMigrator, LoggerInterface $logger, string $supportedTableName, bool $isEeOnly = false)
    {
        $this->tableMigrator = $tableMigrator;
        $this->logger = $logger;
        $this->supportedTableName = $supportedTableName;
        $this->isEeOnly = $isEeOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        if (true === $this->isEeOnly && false === $destinationPim->isEnterpriseEdition()) {
            return;
        }

        $this->logger->info(sprintf('SimpleDataMigrator: Migrate table %s', $this->supportedTableName));
        $this->tableMigrator->migrate($sourcePim, $destinationPim, $this->supportedTableName);
        $this->logger->info(sprintf('SimpleDataMigrator : %s table migrated', $this->supportedTableName));
    }
}
