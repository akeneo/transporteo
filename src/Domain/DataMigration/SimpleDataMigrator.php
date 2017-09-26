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

    public function __construct(TableMigrator $tableMigrator, LoggerInterface $logger, string $supportedTableName)
    {
        $this->tableMigrator = $tableMigrator;
        $this->supportedTableName = $supportedTableName;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->logger->info(sprintf('SimpleDataMigrator: Migrate table %s', $this->supportedTableName));
        $this->tableMigrator->migrate($sourcePim, $destinationPim, $this->supportedTableName);
        $this->logger->info(sprintf('SimpleDataMigrator : %s table migrated', $this->supportedTableName));
    }
}
