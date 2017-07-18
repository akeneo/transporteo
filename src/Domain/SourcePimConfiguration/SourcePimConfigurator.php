<?php
declare(strict_types = 1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;

/**
 * Build the source pim configuration
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
        $composerJson = new ComposerJson($this->fetcher->fetch($pimServerInfo->getComposerJsonPath()));
        $parametersYml = new ParametersYml($this->fetcher->fetch($pimServerInfo->getParametersYmlPath()));

        return new SourcePimConfiguration($composerJson, $parametersYml, $pimServerInfo->getProjectName());
    }
}
