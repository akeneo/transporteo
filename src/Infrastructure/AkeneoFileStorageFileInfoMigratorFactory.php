<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;
use Akeneo\PimMigration\Domain\FilesMigration\AkeneoFileStorageFileInfoMigrator;

/**
 * Factory for AkeneoFileStorageFileInfo.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AkeneoFileStorageFileInfoMigratorFactory
{
    public function createFileStorageFileInfoMigrator(NaiveMigrator $naiveMigrator): AkeneoFileStorageFileInfoMigrator
    {
        return new AkeneoFileStorageFileInfoMigrator($naiveMigrator);
    }
}
