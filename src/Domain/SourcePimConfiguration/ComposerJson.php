<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\AbstractFile;
use Akeneo\PimMigration\Domain\File;
use Ds\Map;
use Ds\Vector;

/**
 * Representation of a composer.json file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ComposerJson extends AbstractFile implements File
{
    public function getRepositoryName(): string
    {
        return $this->getFullContent()['name'];
    }

    public function getDependencies(): Map
    {
        return new Map($this->getFullContent()['require']);
    }

    public function getRepositories(): Vector
    {
        return new Vector($this->getFullContent()['repositories']);
    }

    public static function getFileName(): string
    {
        return 'composer.json';
    }

    protected function loadContent(): array
    {
        return json_decode(file_get_contents($this->getPath()), true);
    }
}
