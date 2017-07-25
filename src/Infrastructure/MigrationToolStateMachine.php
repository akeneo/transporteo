<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

/**
 * State Machine of the application.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationToolStateMachine
{
    /** @var string */
    public $currentState = 'ready';

    /** @var StateMachine */
    protected $stateMachine;

    /** @var array */
    protected $gatheredInformation;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
        $this->gatheredInformation = [];
    }

    public function addToGatheredInformation(string $key, $value): void
    {
        $this->gatheredInformation[$key] = $value;
    }

    public function getGatheredInformation(string $key)
    {
        return $this->gatheredInformation[$key] ?? null;
    }

    public function goNext(): void
    {
        $this->stateMachine->apply($this, $this->getNextTransition()->getName());
    }

    public function start(): void
    {
        while (null !== $this->getNextTransition()) {
            $this->goNext();
        }

        $lastException = $this->getGatheredInformation('lastException');

        if (null !== $lastException) {
            throw $lastException;
        }
    }

    protected function getNextTransition(): ?Transition
    {
        $availableTransitions = $this->stateMachine->getEnabledTransitions($this);

        if ([] === $availableTransitions) {
            return null;
        }

        return $availableTransitions[0];
    }
}
