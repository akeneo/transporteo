<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
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

    /** @var bool */
    protected $useDocker;

    /** @var PimServerInformation */
    protected $sourcePimServerInformation;

    /** @var PimConnection */
    protected $sourcePimConnection;

    /** @var PimConnection */
    protected $destinationPimConnection;

    /** @var DownloadMethod */
    protected $downloadMethod;

    /** @var AkeneoPimClientInterface */
    protected $sourcePimApiClient;

    /** @var AkeneoPimClientInterface */
    protected $destinationPimApiClient;

    /** @var string */
    protected $apiClientId;

    /** @var string */
    protected $apiSecret;

    /** @var string */
    protected $apiUserName;

    /** @var string */
    protected $apiUserPwd;

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

    public function useDocker(): bool
    {
        return $this->destinationPimConnection instanceof DockerConnection;
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

    public function getSourcePimApiClient(): AkeneoPimClientInterface
    {
        return $this->sourcePimApiClient;
    }

    public function setSourcePimApiClient(AkeneoPimClientInterface $sourcePimApiClient): void
    {
        $this->sourcePimApiClient = $sourcePimApiClient;
    }

    public function getDestinationPimApiClient(): AkeneoPimClientInterface
    {
        return $this->destinationPimApiClient;
    }

    public function setDestinationPimApiClient(AkeneoPimClientInterface $destinationPimApiClient): void
    {
        $this->destinationPimApiClient = $destinationPimApiClient;
    }

    public function getApiClientId(): string
    {
        return $this->apiClientId;
    }

    public function setApiClientId($apiClientId): void
    {
        $this->apiClientId = $apiClientId;
    }

    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }

    public function setApiSecret($apiSecret): void
    {
        $this->apiSecret = $apiSecret;
    }

    public function getApiUserName(): string
    {
        return $this->apiUserName;
    }

    public function setApiUserName($apiUserName): void
    {
        $this->apiUserName = $apiUserName;
    }

    public function getApiUserPwd(): string
    {
        return $this->apiUserPwd;
    }

    public function setApiUserPwd($apiUserPwd): void
    {
        $this->apiUserPwd = $apiUserPwd;
    }
}
