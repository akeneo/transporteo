<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

class TransitionResolver
{
    /** @var string */
    private $from;

    /** @var string */
    private $transitionName;

    /** @var callable */
    private $condition;

    public function __construct(string $from, string $transitionName, callable $condition)
    {
        $this->from = $from;
        $this->transitionName = $transitionName;
        $this->condition = $condition;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTransitionName(): string
    {
        return $this->transitionName;
    }

    public function isAppliable(array $context): bool
    {
        return ($this->condition)($context);
    }
}
