<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

/**
 * Keep the PIM server information.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimServerInformation
{
    /** @var string */
    private $composerJsonPath;

    /** @var string */
    private $projectName;

    public function __construct(string $composerJsonPath, string $projectName)
    {
        if (!$this->endsByComposerDotJson($composerJsonPath)) {
            throw new \InvalidArgumentException('ComposerJsonPath must end by '.ComposerJson::getFileName());
        }

        $this->composerJsonPath = $composerJsonPath;
        $this->projectName = $projectName;
    }

    public function getComposerJsonPath(): string
    {
        return $this->composerJsonPath;
    }

    public function getParametersYmlPath(): string
    {
        $path = sprintf('%sapp%sconfig%s%s',
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            ParametersYml::getFileName()
        );

        return pathinfo($this->getComposerJsonPath())['dirname'].$path;
    }

    public function getPimParametersPath(): string
    {
        $path = sprintf('%sapp%sconfig%s%s',
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            PimParameters::getFileName()
        );

        return pathinfo($this->getComposerJsonPath())['dirname'].$path;
    }

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    private function endsByComposerDotJson(string $haystack): bool
    {
        return pathinfo($haystack)['basename'] === ComposerJson::getFileName();
    }
}
