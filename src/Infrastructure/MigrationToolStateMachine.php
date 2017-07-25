<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Ds\Set;
use Symfony\Component\Workflow\StateMachine;

/**
 * State Machine of the application.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MigrationToolStateMachine
{
    public $currentState = 'ready';

    protected $stateMachine;

    protected $gatheredInformation;

    /** @var Set */
    protected $nextStateResolver;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
        $this->gatheredInformation = [];
        $this->buildGoNextResolver();
    }

    public function addToGatheredInformation(string $key, $value): void
    {
        $this->gatheredInformation[$key] = $value;
    }

    public function getGatheredInformation(string $key)
    {
        return $this->gatheredInformation[$key];
    }

    public function goNext(): void
    {
        $this->stateMachine->apply($this, $this->getNextState());
    }

    public function start(): void
    {
        while (null !== $this->getNextState()) {
            $this->goNext();
        }
    }

    protected function getNextState(): ?string
    {
        $availableTransitions = $this->nextStateResolver->filter(function (TransitionResolver $element) {
            return $this->currentState == $element->getFrom();
        })->filter(function (TransitionResolver $element) {
            return $element->isAppliable($this->gatheredInformation);
        });

        if ($availableTransitions->isEmpty()) {
            return null;
        }

        return $availableTransitions->first()->getTransitionName();
    }

    protected function buildGoNextResolver(): void
    {
        $this->nextStateResolver = new Set();

        $this->nextStateResolver->add(new TransitionResolver(
            'ready',
            'ask_source_pim_location',
            function (array $context) {
                return true;
            }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
           'source_pim_location_guessed',
           'local_source_pim_configuration',
           function (array $context) {
               return $context['PimSourceLocation'] === 'local';
           }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
            'source_pim_location_guessed',
            'distant_source_pim_configuration',
            function (array $context) {
                return $context['PimSourceLocation'] === 'server';
            }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
            'source_pim_configured',
            'source_pim_detection',
            function (array $context) {
                return true;
            }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
            'source_pim_detected',
            'ce_access_granted',
            function (array $context) {
                return false === $context['SourcePim']->isEnterpriseEdition();
            }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
            'source_pim_detected',
            'ee_try_ssh_key_already_provided',
            function (array $context) {
                return true === $context['SourcePim']->isEnterpriseEdition() &&
                    true === isset($context['SshKey']);
            }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
            'source_pim_detected',
            'ee_ask_and_try_an_ssh_key',
            function (array $context) {
                return true === $context['SourcePim']->isEnterpriseEdition() &&
                    false === isset($context['SshKey']);
            }
        ));

        $this->nextStateResolver->add(new TransitionResolver(
            'ee_access_pending',
            'ee_grant_access',
            function (array $context) {
                return true === $context['SourcePim']->isEnterpriseEdition() &&
                    true === $context['EeAccessGranted'];
            }
        ));
    }
}
