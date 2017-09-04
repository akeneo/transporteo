<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\StructureMigrator;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S070FromDestinationPimFileDatabaseMigratedToDestinationPimStructureMigrated;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Spec for S70FromDestinationPimFileDatabaseMigratedToDestinationPimStructureMigrated.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S070FromDestinationPimFileDatabaseMigratedToDestinationPimStructureMigratedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        StructureMigrator $migrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $migrator);

        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S070FromDestinationPimFileDatabaseMigratedToDestinationPimStructureMigrated::class);
    }

    public function it_migrates_structure(
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

        $transResult = "Migrating structure data...";
        $translator->trans('from_destination_pim_files_migrated_to_destination_pim_structure_migrated.message')->willReturn($transResult);
        $printerAndAsker->printMessage($transResult)->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimStructureMigration($event);
    }

}
