<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\FilesMigration;

use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Migrate the `akeneo_file_storage_file_info_table`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigrator
{
    /** @var NaiveMigrator */
    private $naiveMigrator;

    public function __construct(NaiveMigrator $naiveMigrator)
    {
        $this->naiveMigrator = $naiveMigrator;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $this->naiveMigrator->migrate($sourcePim, $destinationPim, 'akeneo_file_storage_file_info');
        } catch (\Exception $exception) {
            throw new FilesMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
