<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\ExtraDataMigration;

use Akeneo\PimMigration\Domain\Command\CommandLauncher;
use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\ExtraDataMigration\ExtraDataMigrationException;
use Akeneo\PimMigration\Domain\ExtraDataMigration\ExtraDataMigrator;
use Akeneo\PimMigration\Domain\ExtraDataMigration\ShowTablesCommand;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\UnsuccessfulCommandException;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ExtraDataMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigratorSpec extends ObjectBehavior
{
    public function let(TableMigrator $tableMigrator, CommandLauncher $launcher)
    {
        $this->beConstructedWith(
            $tableMigrator,
            $launcher
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ExtraDataMigrator::class);
    }

    public function it_throws_an_exception_during_getting_table(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $launcher
    ) {
        $launcher->runCommand(new ShowTablesCommand($sourcePim->getWrappedObject()), null, false)->willThrow(new UnsuccessfulCommandException());

        $this->shouldThrow(new ExtraDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_throws_exception_during_migrate(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        UnixCommandResult $commandResult,
        $tableMigrator,
        $launcher
    ) {
        $commandResult->getOutput()->willReturn('an_unknown_table'.PHP_EOL);

        $launcher->runCommand(new ShowTablesCommand($sourcePim->getWrappedObject()), null, false)->willReturn($commandResult);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'an_unknown_table')->willThrow(new DataMigrationException());

        $this->shouldThrow(new ExtraDataMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_migrate_all_tables(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        UnixCommandResult $commandResult,
        $tableMigrator,
        $launcher
    ) {
        $commandResult->getOutput()->willReturn('an_unknown_table'.PHP_EOL);

        $launcher->runCommand(new ShowTablesCommand($sourcePim->getWrappedObject()), null, false)->willReturn($commandResult);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'an_unknown_table')->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
