<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Represent a StateMachine subscriber.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface StateMachineSubscriber extends EventSubscriberInterface
{
    public function setPrinterAndAsker(PrinterAndAsker $asker): void;
}
