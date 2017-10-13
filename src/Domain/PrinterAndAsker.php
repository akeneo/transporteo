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

    public function askSimpleQuestion(string $question, string $default = '', ?callable $validator = null): string;

    public function askHiddenSimpleQuestion(string $question, ?callable $validator = null): string;

    public function title(string $message): void;

    public function section(string $message): void;

    public function note(string $message): void;

    public function printMessage(string $message): void;

    public function warning(string $message): void;
}
