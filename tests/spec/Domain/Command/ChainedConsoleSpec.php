<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\Command;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ChainedConsole.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ChainedConsoleSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ChainedConsole::class);
    }

    public function it_executes_on_supportable_console(
        Console $console1,
        Console $console2,
        PimConnection $sourcePimConnection,
        SourcePim $pim,
        Command $unixCommand
    ) {
        $this->addConsole($console1);
        $this->addConsole($console2);

        $pim->getConnection()->willReturn($sourcePimConnection);

        $console1->supports($sourcePimConnection)->willReturn(false);
        $console2->supports($sourcePimConnection)->willReturn(true);

        $console2->execute($unixCommand, $pim)->shouldBeCalled();

        $this->execute($unixCommand, $pim);
    }

    public function it_throws_an_exception_if_there_is_no_console_supporting_the_connection(
        Console $console1,
        Console $console2,
        PimConnection $pimConnection,
        SourcePim $pim,
        Command $unixCommand
    ) {
        $this->addConsole($console1);
        $this->addConsole($console2);

        $pim->getConnection()->willReturn($pimConnection);

        $console1->supports($pimConnection)->willReturn(false);
        $console2->supports($pimConnection)->willReturn(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('The connection is not supported by any consoles'))
            ->during('execute', [$unixCommand, $pim]);
    }
}
