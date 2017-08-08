<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\DestinationPimDownload\DestinationPim;
use Akeneo\PimMigration\Domain\PimConfiguration\PimConfiguration;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
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
    protected $stateMachineMarker;

    /** @var string */
    protected $projectName;

    /** @var string */
    protected $sourcePimLocation;

    /** @var null|SshKey */
    protected $sshKey;

    /** @var PimConfiguration */
    protected $sourcePimConfiguration;

    /** @var SourcePim */
    protected $sourcePim;

    /** @var DestinationPim */
    protected $destinationPim;

    /** @var string */
    /** @var int */
    protected $destinationPimLocation;

    /** @var string */
    protected $destinationPathPimLocation;

    /** @var string */
    protected $currentDestinationPimLocation;

    /** @var PimConfiguration */
    protected $destinationPimConfiguration;

    public function __construct(StateMachine $stateMachine)
    {
        $this->stateMachineMarker = $stateMachine;
    }

    public function start(): void
    {
        while (null !== $nextTransition = $this->getNextTransition()) {
            $this->stateMachineMarker->apply($this, $nextTransition->getName());
        }
    }

    protected function getNextTransition(): ?Transition
    {
        $availableTransitions = $this->stateMachineMarker->getEnabledTransitions($this);

        if ([] === $availableTransitions) {
            return null;
        }

        return $availableTransitions[0];
    }

    public function setProjectName(string $projectName): void
    {
        $this->projectName = $projectName;
    }

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function setSourcePimLocation(string $sourcePimLocation): void
    {
        $this->sourcePimLocation = $sourcePimLocation;
    }

    public function getSourcePimLocation(): string
    {
        return $this->sourcePimLocation;
    }

    public function getSshKey(): ?SshKey
    {
        return $this->sshKey;
    }

    public function setSshKey(SshKey $sshKey): void
    {
        $this->sshKey = $sshKey;
    }

    public function getSourcePimConfiguration(): PimConfiguration
    {
        return $this->sourcePimConfiguration;
    }

    public function setSourcePimConfiguration(PimConfiguration $sourcePimConfiguration)
    {
        $this->sourcePimConfiguration = $sourcePimConfiguration;
    }

    public function setSourcePim(SourcePim $sourcePim): void
    {
        $this->sourcePim = $sourcePim;
    }

    public function getSourcePim(): SourcePim
    {
        return $this->sourcePim;
    }

    public function setDestinationPim(DestinationPim $destinationPim): void
    {
        $this->destinationPim = $destinationPim;
    }

    public function getDestinationPim(): DestinationPim
    {
        return $this->destinationPim;
    }

    public function setDestinationPimLocation(int $destinationPimLocation): void
    {
        $this->destinationPimLocation = $destinationPimLocation;
    }

    public function getDestinationPimLocation(): int
    {
        return $this->destinationPimLocation;
    }

    public function setDestinationPathPimLocation(string $destinationPath): void
    {
        $this->destinationPathPimLocation = $destinationPath;
    }

    public function getDestinationPathPimLocation(): ?string
    {
        return $this->destinationPathPimLocation ?? null;
    }

    public function setCurrentDestinationPimLocation(string $currentDestinationPimLocation): void
    {
        $this->currentDestinationPimLocation = $currentDestinationPimLocation;
    }

    public function getCurrentDestinationPimLocation(): string
    {
        return $this->currentDestinationPimLocation;
    }

    public function setDestinationPimConfiguration(PimConfiguration $destinationPimConfiguration): void
    {
        $this->destinationPimConfiguration = $destinationPimConfiguration;
    }

    public function getDestinationPimConfiguration(): PimConfiguration
    {
        return $this->destinationPimConfiguration;
    }

}
