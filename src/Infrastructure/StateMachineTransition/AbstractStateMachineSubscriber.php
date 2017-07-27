<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Akeneo\PimMigration\Domain\PrinterAndAsker;

/**
 * Abstract State Machine Subscriber.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var PrinterAndAsker */
    protected $printerAndAsker;

    public function setPrinterAndAsker(PrinterAndAsker $printerAndAsker): void
    {
        $this->printerAndAsker = $printerAndAsker;
    }
}
