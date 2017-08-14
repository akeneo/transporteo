<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DatabaseServices;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Ds\Vector;

/**
 * Migrate from one table to another without any changes.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class NaiveMigrator
{
    /** @var ConnectionBuilder */
    private $connectionBuilder;

    public function __construct(ConnectionBuilder $connectionBuilder)
    {
        $this->connectionBuilder = $connectionBuilder;
    }

    public function migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        string $tableName
    ): void {
        $sourcePimConnection = $this->connectionBuilder->createConnection($sourcePim);
        $destinationPimConnection = $this->connectionBuilder->createConnection($destinationPim);

        $sourcePimRecords = new Vector($sourcePimConnection->fetchAll(sprintf('SELECT * from %s', $tableName)));

        $destinationPimQueryBuilder = $destinationPimConnection->createQueryBuilder();

        $sourcePimRecords->apply(function ($value) use ($destinationPimQueryBuilder, $tableName) {
            $destinationPimQueryBuilder->insert($tableName)->values($value);
        });
    }
}
