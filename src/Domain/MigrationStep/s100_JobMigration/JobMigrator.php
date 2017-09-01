<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;

/**
 * Job migration `batch_execution`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class JobMigrator
{
    /** @var array */
    private $jobMigrators = [];

    /**
     * @throws JobMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        foreach ($this->jobMigrators as $jobMigrator) {
            try {
                $jobMigrator->migrate($sourcePim, $destinationPim);
            } catch (DataMigrationException $exception) {
                throw new JobMigrationException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
    }

    public function addJobMigrator(DataMigrator $jobMigrator): void
    {
        $this->jobMigrators[] = $jobMigrator;
    }
}
