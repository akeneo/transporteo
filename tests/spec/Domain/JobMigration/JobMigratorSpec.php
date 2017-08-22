<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\JobMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\JobMigration\JobMigrationException;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Domain\JobMigration\JobMigrator;
use PhpSpec\ObjectBehavior;

/**
 * Job Migrator Spec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class JobMigratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(JobMigrator::class);
    }

    public function it_calls_several_migrator(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->addJobMigrator($migratorOne);
        $this->addJobMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->shouldBeCalled();

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
