<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\File;
use Ds\Map;

/**
 * Representation of a composer.json file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ComposerJson implements File
{
    /** @var string */
    private $localPath;

    /** @var array */
    private $fullContent;

    public function __construct(string $localPath)
    {
        $this->localPath = $localPath;

        if (file_exists($localPath)) {
            $this->fullContent = json_decode(file_get_contents($this->localPath), true);
        }
    }

    public function getPath(): string
    {
        return $this->localPath;
    }

    public function getRepositoryName(): string
    {
        return $this->fullContent['name'];
    }

    public function getDependencies(): Map
    {
        return new Map($this->fullContent['require']);
    }

    public static function getFileName(): string
    {
        return 'composer.json';
    }
}
