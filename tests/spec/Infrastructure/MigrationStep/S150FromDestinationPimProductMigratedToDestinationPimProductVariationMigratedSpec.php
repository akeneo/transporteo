<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InvalidProductVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S150FromDestinationPimProductMigratedToDestinationPimProductVariationMigrated;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S150FromDestinationPimProductMigratedToDestinationPimProductVariationMigratedSpec extends ObjectBehavior
{
    public function let(Translator $translator, LoggerInterface $logger, ProductVariationMigrator $productVariationMigrator, PrinterAndAsker $printerAndAsker)
    {
        $this->beConstructedWith($translator, $logger, $productVariationMigrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initialisable()
    {
        $this->shouldHaveType(S150FromDestinationPimProductMigratedToDestinationPimProductVariationMigrated::class);
    }

    public function it_migrates_product_variation(
        Event $event,
        TransporteoStateMachine $stateMachine,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $translator,
        $productVariationMigrator,
        $printerAndAsker
    ) {
        $event->getSubject()->willReturn($stateMachine);
        $stateMachine->getSourcePim()->willReturn($sourcePim);
        $stateMachine->getDestinationPim()->willReturn($destinationPim);

        $message = 'Migrating products variations...';

        $translator->trans('from_destination_pim_product_migrated_to_destination_pim_product_variation_migrated.message')->willReturn($message);
        $printerAndAsker->printMessage($message)->shouldBeCalled();

        $productVariationMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimProductVariationMigration($event);
    }
}
