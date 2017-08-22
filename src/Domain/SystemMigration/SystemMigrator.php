<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SystemMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * System migration `user`, `group`, `role`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SystemMigrator
{
    private $systemMigrators = [];

    /**
     * @throws SystemMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        /** @var DataMigrator $systemMigrator */
        foreach ($this->systemMigrators as $systemMigrator) {
            try {
                $systemMigrator->migrate($sourcePim, $destinationPim);
            } catch (DataMigrationException $exception) {
                throw new SystemMigrationException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
    }

    public function addSystemMigrator(DataMigrator $systemMigrator): void
    {
        $this->systemMigrators[] = $systemMigrator;
    }
}
