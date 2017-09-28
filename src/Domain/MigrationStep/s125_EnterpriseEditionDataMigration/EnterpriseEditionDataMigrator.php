<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrator for all Enterprise Edition Data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionDataMigrator implements DataMigrator
{
    /** @var array */
    private $enterpriseEditionDataMigrators = [];

    public function addEnterpriseEditionDataMigrator(DataMigrator $enterpriseEditionDataMigrator): void
    {
        $this->enterpriseEditionDataMigrators[] = $enterpriseEditionDataMigrator;
    }

    /**
     * @throws DataMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            /** @var DataMigrator $enterpriseEditionDataMigrator */
            foreach ($this->enterpriseEditionDataMigrators as $enterpriseEditionDataMigrator) {
                $enterpriseEditionDataMigrator->migrate($sourcePim, $destinationPim);
            }
        } catch (\Exception $exception) {
            throw new EnterpriseEditionDataMigrationException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception);
        }
    }
}
