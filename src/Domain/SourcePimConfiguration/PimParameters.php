<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\AbstractFile;
use Akeneo\PimMigration\Domain\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Representation of a pim_parameters.yml file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimParameters extends AbstractFile implements File
{
    public static function getFileName(): string
    {
        return 'pim_parameters.yml';
    }

    public function getMongoDbInformation(): ?string
    {
        return $this->getFullContent()['mongodb_server'] ?? null;
    }

    public function getMongoDbDatabase(): ?string
    {
        return $this->getFullContent()['mongodb_database'] ?? null;
    }

    protected function loadContent(): array
    {
        return Yaml::parse(file_get_contents($this->getPath()))['parameters'];
    }
}
