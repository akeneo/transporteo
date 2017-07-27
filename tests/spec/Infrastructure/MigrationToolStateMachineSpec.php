<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

/**
 * Spec about the State machine.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationToolStateMachineSpec extends ObjectBehavior
{
    public function let(StateMachine $stateMachine)
    {
        $this->beConstructedWith($stateMachine);
        $this->currentState = 'ready';
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MigrationToolStateMachine::class);
    }

    public function it_does_not_apply_transition_if_there_is_not($stateMachine)
    {
        $stateMachine->getEnabledTransitions($this)->willReturn([]);
        $stateMachine->apply(Argument::any())->shouldNotBeCalled();

        $this->start();
    }

    public function it_apply_transition_if_there_is_one(Transition $transition, $stateMachine)
    {
        $transition->getName()->willReturn('a_transition');
        $stateMachine->getEnabledTransitions($this)->willReturn([$transition]);
        $stateMachine->apply($this, 'a_transition')->shouldBeCalled();
        $stateMachine->getEnabledTransitions($this)->willReturn([]);

        $this->start();
    }
}
