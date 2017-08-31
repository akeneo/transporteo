<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\ReferenceDataMigration\ReferenceDataMigrator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Akeneo\PimMigration\Infrastructure\StateMachineTransition\FromDestinationPimExtraDataMigratedToDestinationPimReferenceDataMigrated;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Spec for FromDestinationPimExtraDataMigratedToDestinationPimReferenceDataMigrated.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FromDestinationPimExtraDataMigratedToDestinationPimReferenceDataMigratedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        ReferenceDataMigrator $migrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $migrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FromDestinationPimExtraDataMigratedToDestinationPimReferenceDataMigrated::class);
    }

    public function it_migrates_reference_data(
        Event $event,
        MigrationToolStateMachine $stateMachine,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator,
        $translator,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getDestinationPim()->willReturn($destinationPim);

        $transMessage = "Migrating reference data...";
        $translator->trans('from_destination_pim_extra_data_migrated_to_destination_pim_reference_data_migrated.message')->willReturn($transMessage);
        $printerAndAsker->printMessage($transMessage)->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimReferenceDataMigration($event);
    }
}
