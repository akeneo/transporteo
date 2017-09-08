<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration;;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrationException;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrator;
use PhpSpec\ObjectBehavior;

/**
 * Job Migrator Spec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class JobMigratorSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console)
    {
        $this->beConstructedWith($console);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(JobMigrator::class);
    }

    public function it_successfully_migrates_jobs(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console
    ) {
        $this->addJobMigrator($migratorOne);
        $this->addJobMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $destinationPim->getDatabaseName()->willReturn('database_name');

        $console->execute(
            new MySqlExecuteCommand('ALTER TABLE database_name.akeneo_batch_job_execution ADD COLUMN raw_parameters LONGTEXT NOT NULL AFTER log_file'),
            $destinationPim
        )->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_throws_an_exception(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->addJobMigrator($migratorOne);
        $this->addJobMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->willThrow(new DataMigrationException());

        $this->shouldThrow(new JobMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }
}
