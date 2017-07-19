<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

/**
 * Keep the PIM server information.
 *
 * @see Step 2
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class PimServerInformation
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $composerJsonPath;

    /** @var string */
    private $username;

    /** @var string */
    private $projectName;

    public function __construct(string $composerJsonPath, string $projectName, ?string $host = null, ?int $port = null, ?string $username = null)
    {
        if (!$this->endsByComposerDotJson($composerJsonPath)) {
            throw new \InvalidArgumentException('ComposerJsonPath must end by '.ComposerJson::getFileName());
        }

        $this->composerJsonPath = $composerJsonPath;
        $this->projectName = $projectName;

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
    }

    public function isLocal(): bool
    {
        return null === $this->host || null === $this->port || null === $this->username;
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

        return pathinfo((string) $this->getComposerJsonPath())['dirname'].$path;
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
