<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\CommandLauncher;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\CheckRequirementsCommand;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimConfigurationChecker;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\IncompatiblePimException;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for destination pim configuration checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimConfigurationCheckerSpec extends ObjectBehavior
{
    public function let(CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($commandLauncher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DestinationPimConfigurationChecker::class);
    }

    public function it_throws_an_exception_when_editions_are_different(
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $destinationPim->isEnterpriseEdition()->willReturn(false);

        $this->shouldThrow(
            new IncompatiblePimException('The source PIM is an Enterprise Edition whereas the destination PIM is not an Enterprise Edition')
        )->during('check', [$sourcePim, $destinationPim]);
    }

    public function it_calls_the_pim_requirements_command(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $commandLauncher
    ) {
        $sourcePim->isEnterpriseEdition()->willReturn(true);
        $destinationPim->isEnterpriseEdition()->willReturn(true);

        $commandLauncher->runCommand(new CheckRequirementsCommand(), $destinationPim)->shouldBeCalled();

        $this->check($sourcePim, $destinationPim);
    }
}
