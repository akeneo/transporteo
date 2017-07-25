<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\StateMachineTransition;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Represent a StateMachine subscriber.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface StateMachineSubscriber extends EventSubscriberInterface
{
    public function setOutput(OutputInterface $output): void;

    public function setInput(InputInterface $input): void;

    public function setQuestionHelper(QuestionHelper $helper): void;
}
