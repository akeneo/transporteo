<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration\EnterpriseEditionDataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S125FromDestinationPimExtraDataMigratedToDestinationPimEnterpriseEditionDataMigrated;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Spec for S125FromDestinationPimExtraDataMigratedToDestinationPimEnterpriseEditionDataMigrated.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S125FromDestinationPimExtraDataMigratedToDestinationPimEnterpriseEditionDataMigratedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        LoggerInterface $logger,
        EnterpriseEditionDataMigrator $migrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $logger, $migrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S125FromDestinationPimExtraDataMigratedToDestinationPimEnterpriseEditionDataMigrated::class);
    }

    public function it_migrates_enterprise_edition_data_for_an_enterprise_edition(
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

        $destinationPim->isEnterpriseEdition()->willReturn(true);

        $transMessage = "Migrating enterprise edition data...";
        $translator->trans('from_destination_pim_extra_data_migrated_to_destination_pim_enterprise_edition_data_migrated.message')->willReturn($transMessage);
        $printerAndAsker->printMessage($transMessage)->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimEnterpriseEditionDataMigration($event);
    }

    public function it_does_nothing_for_a_community_edition(
        Event $event,
        TransporteoStateMachine $stateMachine,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $migrator
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getDestinationPim()->willReturn($destinationPim);

        $destinationPim->isEnterpriseEdition()->willReturn(false);

        $migrator->migrate($sourcePim, $destinationPim)->shouldNotBeCalled();

        $this->onDestinationPimEnterpriseEditionDataMigration($event);
    }
}
