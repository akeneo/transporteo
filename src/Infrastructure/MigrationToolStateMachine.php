<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DownloadMethod;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Infrastructure\Pim\DockerConnection;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    /** @var ContainerBuilder */
    protected $container;

    /** @var PimConnection */
    protected $sourcePimConnection;

    /** @var PimConnection */
    protected $destinationPimConnection;

    public function __construct(StateMachine $stateMachine, Container $container)
    {
        $this->stateMachineMarker = $stateMachine;
        $this->container = $container;
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

        $this->makeAwareOf('migration_tool.source_pim_connection_aware', 'connectSourcePim', $connection);

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

        $this->makeAwareOf('migration_tool.destination_pim_connection_aware', 'connectDestinationPim', $connection);
    }

    public function setDownloadMethod(DownloadMethod $downloadMethod): void
    {
        $this->downloadMethod = $downloadMethod;

        $this->makeAwareOf('migration_tool.destinatiom_pim_download_method_aware', 'setDownloadMethod', $downloadMethod);
    }

    protected function makeAwareOf(string $tagToAware, string $awareMethod, $objectToAware)
    {
        $servicesToAware = $this->container->findTaggedServiceIds($tagToAware);

        foreach ($servicesToAware as $id => $tags) {
            $this->container->get($id)->$awareMethod($objectToAware);
        }
    }
}
