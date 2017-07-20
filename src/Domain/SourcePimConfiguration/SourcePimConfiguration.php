<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

/**
 * Global configuration of PIM source.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class SourcePimConfiguration
class SourcePimConfiguration
{
    /** @var ComposerJson */
    private $composerJson;

    /** @var ParametersYml */
    private $parametersYml;

    /** @var string */
    private $projectName;

    public function __construct(ComposerJson $composerJson, ParametersYml $parametersYml, string $projectName)
    {
        $this->composerJson = $composerJson;
        $this->parametersYml = $parametersYml;
        $this->projectName = $projectName;
    }

    public function getComposerJson(): ComposerJson
    {
        return $this->composerJson;
    }

    public function getParametersYml(): ParametersYml
    {
        return $this->parametersYml;
    }
}
