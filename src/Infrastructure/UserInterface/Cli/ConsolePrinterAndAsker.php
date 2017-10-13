<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsolePrinterAndAsker implements PrinterAndAsker
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var QuestionHelper */
    private $questionHelper;

    /** @var  */
    private $io;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
        $this->io = new SymfonyStyle($input, $output);
    }

    public function askChoiceQuestion(string $question, array $choicesAvailable): string
    {
        return $this->io->choice($question, $choicesAvailable);
    }

    public function askSimpleQuestion(string $question, string $default = '', ?callable $validator = null): string
    {
        return $this->io->ask($question, $default, function ($answer) use ($validator) {
            if (empty(trim($answer))) {
                throw new \RuntimeException('Please provide a value :)');
            }

            if (null !== $validator) {
                $validator($answer);
            }

            return $answer;
        }
        );
    }

    public function askHiddenSimpleQuestion(string $question, ?callable $validator = null): string
    {
        return $this->io->askHidden($question, function ($answer) use ($validator) {
            if (empty(trim($answer))) {
                throw new \RuntimeException('Please provide a value :)');
            }

            if (null !== $validator) {
                $validator($answer);
            }

            return $answer;
        }
        );
    }

    public function title(string $message): void
    {
        $this->io->title($message);
    }

    public function section(string $message): void
    {
        $this->io->section($message);
    }

    public function note(string $message): void
    {
        $this->io->note($message);
    }

    public function printMessage(string $message): void
    {
        $this->io->writeln($message);
    }

    public function warning(string $message): void
    {
        $this->io->warning($message);
    }
}
