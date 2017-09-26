<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;

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

    /** @var Translator */
    protected $translator;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(Translator $translator, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function setPrinterAndAsker(PrinterAndAsker $printerAndAsker): void
    {
        $this->printerAndAsker = $printerAndAsker;
    }

    protected function getClassName(): string
    {
        return substr(static::class, strrpos(static::class, '\\') + 1);
    }

    protected function logEntering(string $methodName): void
    {
        $this->logger->debug(sprintf('%s: entering %s', $this->getClassName(), $methodName));
    }

    protected function logExit(string $methodName): void
    {
        $this->logger->debug(sprintf('%s: %s finished', $this->getClassName(), $methodName));
    }

    protected function logGuardEntering(string $methodName): void
    {
        $this->logger->debug(sprintf('%s: entering guard %s', $this->getClassName(), $methodName));
    }

    protected function logGuardResult(string $methodName, bool $isBlocking): void
    {
        $this->logger->debug(sprintf('%s: guard %s, %s access', $this->getClassName(), $methodName, $isBlocking ? 'blocking' : 'allowing'));
    }
}
