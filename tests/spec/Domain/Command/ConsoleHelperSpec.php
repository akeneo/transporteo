<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Command\UnixCommand;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ConsoleHelper.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ConsoleHelperSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ConsoleHelper::class);
    }

    public function it_executes_on_supportable_console(
        Console $console1,
        Console $console2,
        PimConnection $sourcePimConnection,
        SourcePim $pim,
        UnixCommand $unixCommand
    ) {
        $this->addConsole($console1);
        $this->addConsole($console2);
        $this->connectSourcePim($sourcePimConnection);

        $console1->supports($sourcePimConnection)->willReturn(false);
        $console2->supports($sourcePimConnection)->willReturn(true);

        $console2->execute($unixCommand, $pim, $sourcePimConnection)->shouldBeCalled();

        $this->execute($pim, $unixCommand);
    }

    public function it_throws_an_exception_if_there_is_no_console_supporting_the_connection(
        Console $console1,
        Console $console2,
        PimConnection $pimConnection,
        SourcePim $pim,
        UnixCommand $unixCommand
    ) {
        $this->addConsole($console1);
        $this->addConsole($console2);
        $this->connectSourcePim($pimConnection);

        $console1->supports($pimConnection)->willReturn(false);
        $console2->supports($pimConnection)->willReturn(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('The connection is not supported by any consoles'))
            ->during('execute', [$pim, $unixCommand]);
    }
}
