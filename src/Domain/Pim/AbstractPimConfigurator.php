<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

use Akeneo\PimMigration\Domain\FileNotFoundException;
use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurationException;
use Ds\Map;

/**
 * Build the pim configuration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractPimConfigurator implements PimConfigurator
{
    public function configure(PimConnection $connection, PimServerInformation $pimServerInfo): PimConfiguration
    {
        $filesToFetch = new Map([
            ComposerJson::class => $pimServerInfo->getComposerJsonPath(),
            ParametersYml::class => $pimServerInfo->getParametersYmlPath(),
            PimParameters::class => $pimServerInfo->getPimParametersPath(),
        ]);

        $fetchedFile = $filesToFetch
            ->map(function (string $class, string $path) use ($connection) {
                try {
                    return new $class($this->fetch($connection, $path));
                } catch (FileNotFoundException $exception) {
                    throw new SourcePimConfigurationException(
                        "The file {$exception->getFilePath()} is not reachable or readable",
                        0,
                        $exception
                    );
                }
            });

        return new PimConfiguration(
            $fetchedFile->get(ComposerJson::class),
            $fetchedFile->get(ParametersYml::class),
            $fetchedFile->get(PimParameters::class),
            $pimServerInfo->getProjectName()
        );
    }

    abstract protected function fetch(PimConnection $connection, string $path): string;
}
