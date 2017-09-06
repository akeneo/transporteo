<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\DataMigration\EntityMappingException;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Akeneo\PimMigration\Domain\Command\UnsuccessfulCommandException;
use Akeneo\PimMigration\Domain\DataMigration\EntityMappingChecker;
use PhpSpec\ObjectBehavior;

/**
 * Spec for Entity Mapping Checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EntityMappingCheckerSpec extends ObjectBehavior
{
    public function let(ConsoleHelper $consoleHelper)
    {
        $this->beConstructedWith($consoleHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(EntityMappingChecker::class);
    }

    public function it_does_nothing_if_its_green(
        Pim $pim,
        UnixCommandResult $commandResult,
        $consoleHelper
    ) {
        $resultOutput = <<<TXT
Found 53 mapped entities:
[OK]   Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure
[OK]   Oro\Bundle\ConfigBundle\Entity\Config
[OK]   Pim\Bundle\CatalogBundle\Entity\Family

TXT;
        $commandResult->getOutput()->willReturn($resultOutput);
        $pim->absolutePath()->willReturn('/a-path');
        $consoleHelper->execute($pim, new SymfonyCommand('doctrine:mapping:info'))->willReturn($commandResult);

        $this->check($pim, 'Pim\Bundle\CatalogBundle\Entity\Family');
    }

    public function it_throws_an_exception_for_a_non_existing_entity(
        Pim $pim,
        UnixCommandResult $commandResult,
        $consoleHelper
    ) {
        $resultOutput = <<<TXT
Found 53 mapped entities:
[OK]   Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure
[OK]   Oro\Bundle\ConfigBundle\Entity\Config
[KO]   Pim\Bundle\CatalogBundle\Entity\Family

TXT;
        $commandResult->getOutput()->willReturn($resultOutput);
        $pim->absolutePath()->willReturn('/a-path');
        $consoleHelper->execute($pim, new SymfonyCommand('doctrine:mapping:info'))->willReturn($commandResult);

        $this
            ->shouldThrow(
                new EntityMappingException('The entity Pim\Bundle\CatalogBundle\Entity\Family is not well mapped, please check your mapping')
            )
            ->during('check', [$pim, 'Pim\Bundle\CatalogBundle\Entity\Family']);
    }

    public function it_throws_an_exception_if_the_mapping_is_not_ok(
        Pim $pim,
        UnixCommandResult $commandResult,
        $consoleHelper
    )
    {
        $resultOutput = <<<TXT
Found 53 mapped entities:
[OK]   Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure
[OK]   Oro\Bundle\ConfigBundle\Entity\Config
[OK]   Pim\Bundle\CatalogBundle\Entity\Family

TXT;
        $commandResult->getOutput()->willReturn($resultOutput);
        $pim->absolutePath()->willReturn('/a-path');
        $consoleHelper->execute($pim, new SymfonyCommand('doctrine:mapping:info'))->willReturn($commandResult);

        $this
            ->shouldThrow(
                new \InvalidArgumentException('The entity Pim\Bundle\CatalogBundle\Entity\ANonExistingEntity is not configured')
            )
            ->during('check', [$pim, 'Pim\Bundle\CatalogBundle\Entity\ANonExistingEntity']);
    }

    public function it_throws_an_exception_due_to_command_launcher(
        Pim $pim,
        $consoleHelper
    ) {
        $pimPath = '/a-path';
        $pim->absolutePath()->willReturn($pimPath);

        $consoleHelper->execute($pim, new SymfonyCommand('doctrine:mapping:info'))->willThrow(UnsuccessfulCommandException::class);

        $this->shouldThrow(new EntityMappingException())->during('check', [$pim, 'AnEntity']);
    }
}
