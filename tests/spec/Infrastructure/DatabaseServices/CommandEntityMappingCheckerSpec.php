<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\EntityMappingException;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\UnixCommandResult;
use Akeneo\PimMigration\Infrastructure\Command\UnsuccessfulCommandException;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\CommandEntityMappingChecker;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\DoctrineMappingInfoCommand;
use PhpSpec\ObjectBehavior;

/**
 * Spec for Entity Mapping Checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandEntityMappingCheckerSpec extends ObjectBehavior
{
    public function let(CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($commandLauncher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CommandEntityMappingChecker::class);
    }

    public function it_does_nothing_if_its_green(
        AbstractPim $pim,
        UnixCommandResult $commandResult,
        $commandLauncher
    ) {
        $resultOutput = <<<TXT
Found 53 mapped entities:
[OK]   Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure
[OK]   Oro\Bundle\ConfigBundle\Entity\Config
[OK]   Pim\Bundle\CatalogBundle\Entity\Family

TXT;
        $commandResult->getOutput()->willReturn($resultOutput);
        $pim->getPath()->willReturn('/a-path');
        $commandLauncher->runCommand(new DoctrineMappingInfoCommand(), '/a-path', false)->willReturn($commandResult);

        $this->check($pim, 'Pim\Bundle\CatalogBundle\Entity\Family');
    }

    public function it_throws_an_exception_for_a_non_existing_entity(
        AbstractPim $pim,
        UnixCommandResult $commandResult,
        $commandLauncher
    ) {
        $resultOutput = <<<TXT
Found 53 mapped entities:
[OK]   Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure
[OK]   Oro\Bundle\ConfigBundle\Entity\Config
[KO]   Pim\Bundle\CatalogBundle\Entity\Family

TXT;
        $commandResult->getOutput()->willReturn($resultOutput);
        $pim->getPath()->willReturn('/a-path');
        $commandLauncher->runCommand(new DoctrineMappingInfoCommand(), '/a-path', false)->willReturn($commandResult);

        $this
            ->shouldThrow(
                new EntityMappingException('The entity Pim\Bundle\CatalogBundle\Entity\Family is not well mapped, please check your mapping')
            )
            ->during('check', [$pim, 'Pim\Bundle\CatalogBundle\Entity\Family']);
    }

    public function it_throws_an_exception_if_the_mapping_is_not_ok(
        AbstractPim $pim,
        UnixCommandResult $commandResult,
        $commandLauncher
    )
    {
        $resultOutput = <<<TXT
Found 53 mapped entities:
[OK]   Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure
[OK]   Oro\Bundle\ConfigBundle\Entity\Config
[OK]   Pim\Bundle\CatalogBundle\Entity\Family

TXT;
        $commandResult->getOutput()->willReturn($resultOutput);
        $pim->getPath()->willReturn('/a-path');
        $commandLauncher->runCommand(new DoctrineMappingInfoCommand(), '/a-path', false)->willReturn($commandResult);

        $this
            ->shouldThrow(
                new \InvalidArgumentException('The entity Pim\Bundle\CatalogBundle\Entity\ANonExistingEntity is not configured')
            )
            ->during('check', [$pim, 'Pim\Bundle\CatalogBundle\Entity\ANonExistingEntity']);
    }

    public function it_throws_an_exception_due_to_command_launcher(
        AbstractPim $pim,
        $commandLauncher
    ) {
        $pimPath = '/a-path';
        $pim->getPath()->willReturn($pimPath);

        $commandLauncher->runCommand(
            new DoctrineMappingInfoCommand(),
            $pimPath,
            false
        )->willThrow(UnsuccessfulCommandException::class);

        $this->shouldThrow(new EntityMappingException())->during('check', [$pim, 'AnEntity']);
    }
}
