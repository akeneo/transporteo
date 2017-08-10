<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\UserInterface\Cli;

use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConsolePrinterAndAsker implements PrinterAndAsker
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var QuestionHelper */
    private $questionHelper;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
    }

    public function askChoiceQuestion(string $question, array $choicesAvailable): string
    {
        return $this->questionHelper->ask(
            $this->input,
            $this->output,
            new ChoiceQuestion('<question>'.$question.'</question>', $choicesAvailable)
        );
    }

    public function askSimpleQuestion(string $question, string $default = '', ?callable $validator = null): string
    {
        return $this->questionHelper->ask(
            $this->input,
            $this->output,
            (new Question('<question>'.$question.'</question>', $default))->setValidator(function ($answer) use ($validator) {
                if (empty(trim($answer))) {
                    throw new \RuntimeException('Please provide a value :)');
                }

                if (null !== $validator) {
                    $validator($answer);
                }

                return $answer;
            })
        );
    }

    public function printMessage(string $message): void
    {
        $this->output->writeln('<info>'.$message.'</info>');
    }

    public function getBoldQuestionWords(string $words): string
    {
        return '<options=bold;bg=cyan;fg=black>'.$words.'</>';
    }
}
