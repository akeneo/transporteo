<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration\GroupMigrator;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use Akeneo\PimMigration\Infrastructure\StateMachineTransition\S110FromDestinationPimJobMigratedToDestinationPimGroupMigrated;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S110FromDestinationPimJobMigratedToDestinationPimGroupMigratedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        GroupMigrator $migrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $migrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S110FromDestinationPimJobMigratedToDestinationPimGroupMigrated::class);
    }

    public function it_migrates_groups_on_destination_pim(
        Event $event,
        TransporteoStateMachine $stateMachine,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator,
        $translator,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getDestinationPim()->willReturn($destinationPim);

        $transMessage = "Migrating groups data...";
        $translator->trans('from_destination_pim_job_migrated_to_destination_pim_group_migrated.message')->willReturn($transMessage);
        $printerAndAsker->printMessage($transMessage)->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimGroupMigration($event);
    }
}
