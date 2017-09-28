<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\DataMigration\SimpleDataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * Spec for Simple Migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SimpleDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $migrator, LoggerInterface $logger) {
        $this->beConstructedWith($migrator, $logger, 'table_test');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SimpleDataMigrator::class);
    }

    public function it_migrates_an_enterprise_edition(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    )
    {
        $destinationPim->isEnterpriseEdition()->willReturn(true);

        $migrator->migrate($sourcePim, $destinationPim, 'table_test')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_migrates_a_community_edition(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    )
    {
        $destinationPim->isEnterpriseEdition()->willReturn(false);

        $migrator->migrate($sourcePim, $destinationPim, 'table_test')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_a_ee_table_if_is_not(
        TableMigrator $migrator,
        LoggerInterface $logger,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->beConstructedWith($migrator, $logger, 'table_test', true);
        $destinationPim->isEnterpriseEdition()->willReturn(false);

        $migrator->migrate($sourcePim, $destinationPim, 'table_test')->shouldNotBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_migrates_an_enterprise_edition_for_an_ee_only_migrator(
        TableMigrator $migrator,
        LoggerInterface $logger,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->beConstructedWith($migrator, $logger, 'table_test', true);
        $destinationPim->isEnterpriseEdition()->willReturn(true);

        $migrator->migrate($sourcePim, $destinationPim, 'table_test')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
