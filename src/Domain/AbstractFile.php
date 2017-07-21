<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

/**
 * AbstractFile to gather common logic.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractFile implements File
{
    /** @var string */
    private $localPath;

    /** @var array */
    private $fullContent;

    public function __construct(string $localPath)
    {
        if (!file_exists($localPath) || !is_readable($localPath)) {
            throw new FileNotFoundException("The file {$localPath} does not exist or is not readable");
        }

        $this->localPath = $localPath;
        $this->fullContent = $this->loadContent();
    }

    public function getPath(): string
    {
        return $this->localPath;
    }

    protected function getFullContent(): array
    {
        return $this->fullContent;
    }

    abstract protected function loadContent(): array;
}
