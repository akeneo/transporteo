<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimApiParameters;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;

/**
 * State Machine of the application.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class TransporteoStateMachine
{
    /** @var string */
    public $currentState = 'ready';

    /** @var StateMachine */
    protected $stateMachineMarker;

    /** @var string|null */
    protected $projectName = null;

    /** @var string */
    protected $sourcePimLocation;

    /** @var null|SshKey */
    protected $enterpriseAccessAllowedKey;

    /** @var PimConfiguration */
    protected $sourcePimConfiguration;

    /** @var SourcePim */
    protected $sourcePim;

    /** @var DestinationPim */
    protected $destinationPim;

    /** @var int */
    protected $destinationPimLocation;

    /** @var string */
    protected $destinationPathPimLocation;

    /** @var string */
    protected $currentDestinationPimLocation;

    /** @var PimConfiguration */
    protected $destinationPimConfiguration;

    /** @var PimServerInformation */
    protected $sourcePimServerInformation;

    /** @var PimConnection */
    protected $sourcePimConnection;

    /** @var PimConnection */
    protected $destinationPimConnection;

    /** @var DownloadMethod */
    protected $downloadMethod;

    /** @var PimApiParameters */
    protected $sourcePimApiParameters;

    /** @var PimApiParameters */
    protected $destinationPimApiParameters;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(StateMachine $stateMachine, LoggerInterface $logger)
    {
        $this->stateMachineMarker = $stateMachine;
        $this->logger = $logger;
    }

    public function start(): void
    {
        while (null !== $nextTransition = $this->getNextTransition()) {
            $this->logger->debug(sprintf('STATE MACHINE: apply %s transition', $nextTransition->getName()));
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

    public function getProjectName(): ?string
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

    public function getEnterpriseAccessAllowedKey(): ?SshKey
    {
        return $this->enterpriseAccessAllowedKey;
    }

    public function setEnterpriseAccessAllowedKey(SshKey $sshKey): void
    {
        $this->enterpriseAccessAllowedKey = $sshKey;
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

    public function setSourcePimServerInformation(PimServerInformation $pimServerInformation): void
    {
        $this->sourcePimServerInformation = $pimServerInformation;
    }

    public function getSourcePimRealPath(): string
    {
        return str_replace(DIRECTORY_SEPARATOR.'composer.json', '', $this->sourcePimServerInformation->getComposerJsonPath());
    }

    public function setSourcePimConnection(PimConnection $connection): void
    {
        $this->sourcePimConnection = $connection;

        if ($connection instanceof SshConnection) {
            $this->setEnterpriseAccessAllowedKey($connection->getSshKey());
        }
    }

    public function getSourcePimConnection(): PimConnection
    {
        return $this->sourcePimConnection;
    }

    public function setDestinationPimConnection(PimConnection $connection): void
    {
        $this->destinationPimConnection = $connection;
    }

    public function getDestinationPimConnection(): PimConnection
    {
        return $this->destinationPimConnection;
    }

    public function setDownloadMethod(DownloadMethod $downloadMethod): void
    {
        $this->downloadMethod = $downloadMethod;
    }

    public function getDownloadMethod(): DownloadMethod
    {
        return $this->downloadMethod;
    }

    public function getSourcePimApiParameters(): PimApiParameters
    {
        return $this->sourcePimApiParameters;
    }

    public function setSourcePimApiParameters(PimApiParameters $sourcePimApiParameters): void
    {
        $this->sourcePimApiParameters = $sourcePimApiParameters;
    }

    public function getDestinationPimApiParameters(): PimApiParameters
    {
        return $this->destinationPimApiParameters;
    }

    public function setDestinationPimApiParameters(PimApiParameters $destinationPimApiParameters): void
    {
        $this->destinationPimApiParameters = $destinationPimApiParameters;
    }
}
