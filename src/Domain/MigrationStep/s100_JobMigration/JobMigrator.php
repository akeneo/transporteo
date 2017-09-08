<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

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

    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    /**
     * @throws JobMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            foreach ($this->jobMigrators as $jobMigrator) {
                $jobMigrator->migrate($sourcePim, $destinationPim);
            }

            $query = sprintf(
                'ALTER TABLE %s.akeneo_batch_job_execution ADD COLUMN raw_parameters LONGTEXT NOT NULL AFTER log_file',
                $destinationPim->getDatabaseName()
            );

            $this->console->execute(new MySqlExecuteCommand($query), $destinationPim);
        } catch (DataMigrationException $exception) {
            throw new JobMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function addJobMigrator(DataMigrator $jobMigrator): void
    {
        $this->jobMigrators[] = $jobMigrator;
    }
}
