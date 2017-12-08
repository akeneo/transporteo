<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimCheckConfigurationException;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimVersionChecker;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimVersionCheckerSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console)
    {
        $this->beConstructedWith($console);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DestinationPimVersionChecker::class);
    }

    public function it_checks_an_acceptable_version(DestinationPim $pim, CommandResult $commandResult, $console)
    {
        $console->execute(new SymfonyCommand('pim:system:information'), $pim)->willReturn($commandResult);

        $commandResult->getOutput()->willReturn('| Version  | 2.0.3 ');

        $this->check($pim);
    }

    public function it_throws_an_exception_if_the_version_it_fails_to_read_the_version(DestinationPim $pim, CommandResult $commandResult, $console)
    {
        $console->execute(new SymfonyCommand('pim:system:information'), $pim)->willReturn($commandResult);

        $commandResult->getOutput()->willReturn('Version : unknown');

        $this->shouldThrow(new DestinationPimCheckConfigurationException('Failed to read the destination PIM version.'))
            ->during('check', [$pim]);
    }

    public function it_throws_an_exception_if_the_version_is_not_supported(DestinationPim $pim, CommandResult $commandResult, $console)
    {
        $console->execute(new SymfonyCommand('pim:system:information'), $pim)->willReturn($commandResult);

        $commandResult->getOutput()->willReturn('| Version  | 2.1.0 ');

        $this->shouldThrow(new DestinationPimCheckConfigurationException(sprintf(
            'The current version of your destination PIM 2.1.0 is not supported. The version should be %d.%d.x',
            DestinationPimVersionChecker::EXACT_MAJOR_VERSION,
            DestinationPimVersionChecker::EXACT_MINOR_VERSION
        )))->during('check', [$pim]);
    }

    public function it_throws_an_exception_if_the_version_patch_is_below_the_minimum(DestinationPim $pim, CommandResult $commandResult, $console)
    {
        $console->execute(new SymfonyCommand('pim:system:information'), $pim)->willReturn($commandResult);

        $commandResult->getOutput()->willReturn('| Version  | 2.0.2 ');

        $this->shouldThrow(new DestinationPimCheckConfigurationException(sprintf(
            'The current version of your destination PIM 2.0.2 is not supported. The minimum version of the destination PIM is %d.%d.%d',
            DestinationPimVersionChecker::EXACT_MAJOR_VERSION,
            DestinationPimVersionChecker::EXACT_MINOR_VERSION,
            DestinationPimVersionChecker::MINIMUM_PATCH_VERSION
        )))->during('check', [$pim]);
    }
}
