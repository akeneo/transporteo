<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\UnixCommandResult;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\CommandTableNamesFetcher;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\ShowTablesCommand;
use PhpSpec\ObjectBehavior;

/**
 * Spec for command table names fetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandTableNamesFetcherSpec extends ObjectBehavior
{
    public function let(CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($commandLauncher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CommandTableNamesFetcher::class);
    }

    public function it_returns_table_names(
        SourcePim $sourcePim,
        UnixCommandResult $commandResult,
        $commandLauncher
    ) {
        $commandResult->getOutput()->willReturn('a_table'.PHP_EOL.'an_other_table'.PHP_EOL.' ');

        $commandLauncher->runCommand(new ShowTablesCommand($sourcePim->getWrappedObject()), null, false)->willReturn($commandResult);

        $this->getTableNames($sourcePim)->shouldReturn(['a_table', 'an_other_table']);
    }
}
