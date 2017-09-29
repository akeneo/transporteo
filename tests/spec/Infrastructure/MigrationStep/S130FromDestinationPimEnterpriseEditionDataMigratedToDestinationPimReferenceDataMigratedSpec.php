<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration\ReferenceDataMigrator;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S130FromDestinationPimEnterpriseEditionDataMigratedToDestinationPimReferenceDataMigrated;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Spec for FromDestinationPimExtraDataMigratedToDestinationPimReferenceDataMigrated.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S130FromDestinationPimEnterpriseEditionDataMigratedToDestinationPimReferenceDataMigratedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        LoggerInterface $logger,
        ReferenceDataMigrator $migrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $logger, $migrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S130FromDestinationPimEnterpriseEditionDataMigratedToDestinationPimReferenceDataMigrated::class);
    }

    public function it_migrates_reference_data(
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

        $transMessage = "Migrating reference data...";
        $translator->trans('from_destination_pim_enterprise_edition_data_migrated_to_destination_pim_reference_data_migrated.message')->willReturn($transMessage);
        $printerAndAsker->printMessage($transMessage)->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimReferenceDataMigration($event);
    }
}
