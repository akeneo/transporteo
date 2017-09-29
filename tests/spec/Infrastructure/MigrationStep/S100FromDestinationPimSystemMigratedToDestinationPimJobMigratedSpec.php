<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S100FromDestinationPimSystemMigratedToDestinationPimJobMigrated;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Spec for FromDestinationPimSystemMigratedToDestinationPimJobMigrated.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S100FromDestinationPimSystemMigratedToDestinationPimJobMigratedSpec extends ObjectBehavior
{
    public function let(
        Translator $translator,
        LoggerInterface $logger,
        JobMigrator $migrator,
        PrinterAndAsker $printerAndAsker
    ) {
        $this->beConstructedWith($translator, $logger, $migrator);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S100FromDestinationPimSystemMigratedToDestinationPimJobMigrated::class);
    }

    public function it_migrates_jobs(
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

        $transResult = "Migrating jobs data...";
        $translator->trans('from_destination_pim_system_migrated_to_destination_pim_job_migrated.message')->willReturn($transResult);
        $printerAndAsker->printMessage($transResult)->shouldBeCalled();

        $migrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $this->onDestinationPimJobMigration($event);
    }
}
