<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\FilesMigration;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Ds\Vector;

/**
 * Migrate the `akeneo_file_storage_file_info_table`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigrator
{
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $sourcePimConnection = DriverManager::getConnection($sourcePim->getDatabaseConnectionParams(), new Configuration());
        $sourcePimConnection->connect();
        $sourcePimRecords = new Vector($sourcePimConnection->fetchAll('SELECT * FROM akeneo_file_storage_file_info'));

        $destinationPimConnection = DriverManager::getConnection($destinationPim->getDatabaseConnectionParams(), new Configuration());
        $destinationPimConnection->connect();

        $destinationPimQueryBuilder = $destinationPimConnection->createQueryBuilder();

        $sourcePimRecords->apply(
            function ($value) use ($destinationPimQueryBuilder) {
                try {
                    $destinationPimQueryBuilder->insert('akeneo_file_storage_file_info')->values($value);
                } catch (\Exception $exception) {
                    throw new FilesMigrationException($exception->getMessage(), $exception->getCode(), $exception);
                }
            }
        );
    }
}
