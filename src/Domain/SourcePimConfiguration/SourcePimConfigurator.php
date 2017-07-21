<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileNotFoundException;
use Ds\Map;

/**
 * Build the source pim configuration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimConfigurator
{
    /** @var FileFetcher */
    private $fetcher;

    public function __construct(FileFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    public function configure(PimServerInformation $pimServerInfo): SourcePimConfiguration
    {
        $filesToFetch = new Map([
            ComposerJson::class => $pimServerInfo->getComposerJsonPath(),
            ParametersYml::class => $pimServerInfo->getParametersYmlPath(),
            PimParameters::class => $pimServerInfo->getPimParametersPath(),
        ]);

        if (null !== $pimServerInfo->getSshKeyPath()) {
            $filesToFetch->put(SshKey::class, $pimServerInfo->getSshKeyPath());
        }

        $fetchedFile = $filesToFetch
            ->map(function (string $class, string $path) {
                try {
                    return new $class($this->fetcher->fetch($path));
                } catch (FileNotFoundException $exception) {
                    throw new SourcePimConfigurationException(
                        "The file {$exception->getFilePath()} is not reachable or readable",
                        0,
                        $exception
                    );
                }
            });

        return new SourcePimConfiguration(
            $fetchedFile->get(ComposerJson::class),
            $fetchedFile->get(ParametersYml::class),
            $fetchedFile->get(PimParameters::class),
            $fetchedFile->get(SshKey::class, null),
            $pimServerInfo->getProjectName()
        );
    }
}
