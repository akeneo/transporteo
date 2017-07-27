<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

/**
 * Interface for asking question.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface PrinterAndAsker
{
    public function askChoiceQuestion(string $question, array $choicesAvailable): string;

    public function askSimpleQuestion(string $question, ?string $default = null): string;

    public function printMessage(string $message): void;
}
