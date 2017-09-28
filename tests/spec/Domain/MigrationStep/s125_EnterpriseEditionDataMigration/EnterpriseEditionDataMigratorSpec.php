<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration\EnterpriseEditionDataMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration\EnterpriseEditionDataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for EnterpriseEditionDataMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionDataMigratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(EnterpriseEditionDataMigrator::class);
    }

    public function it_calls_several_migrator(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->addEnterpriseEditionDataMigrator($migratorOne);
        $this->addEnterpriseEditionDataMigrator($migratorTwo);

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
        $this->addEnterpriseEditionDataMigrator($migratorOne);
        $this->addEnterpriseEditionDataMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->willThrow(new DataMigrationException());

        $this->shouldThrow(new EnterpriseEditionDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }
}
