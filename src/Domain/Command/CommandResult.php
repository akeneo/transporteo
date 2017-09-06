<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Represent a result after the execution of a Command on a console.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandResult
{
    /** @var int */
    private $resultCode;

    /** @var string */
    private $output;

    public function __construct(int $resultCode, string $output)
    {
        $this->resultCode = $resultCode;
        $this->output = $output;
    }

    /**
     * @return int
     */
    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }
}
