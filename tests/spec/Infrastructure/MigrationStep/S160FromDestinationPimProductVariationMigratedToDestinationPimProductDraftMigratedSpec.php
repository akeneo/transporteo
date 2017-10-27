<?php

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration\ProductDraftMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S160FromDestinationPimProductVariationMigratedToDestinationPimProductDraftMigrated;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

class S160FromDestinationPimProductVariationMigratedToDestinationPimProductDraftMigratedSpec extends ObjectBehavior
{
    function let(
        Translator $translator,
        LoggerInterface $logger,
        ProductDraftMigrator $productDraftMigrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $logger, $productDraftMigrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            S160FromDestinationPimProductVariationMigratedToDestinationPimProductDraftMigrated::class
        );
    }

    function it_migrates_drafts_and_proposals(
        Event $event,
        TransporteoStateMachine $stateMachine,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $translator,
        $printerAndAsker,
        $productDraftMigrator
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getDestinationPim()->willReturn($destinationPim);

        $transMessage = "Migrating products drafts and proposals...";
        $translator->trans(
            'from_destination_pim_product_variation_migrated_to_destination_pim_product_draft_migrated.message'
        )->willReturn($transMessage);
        $printerAndAsker->printMessage($transMessage)->shouldBeCalled();

        $productDraftMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimProductDraftMigration($event);
    }
}
