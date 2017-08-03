<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\PimConfiguration;

use Akeneo\PimMigration\Domain\AbstractFile;
use Akeneo\PimMigration\Domain\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Representation of a parameters.yml file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ParametersYml extends AbstractFile implements File
{
    public static function getFileName(): string
    {
        return 'parameters.yml';
    }

    public function getDatabaseHost(): string
    {
        return $this->getFullContent()['database_host'];
    }

    public function getDatabasePort(): ?int
    {
        return $this->getFullContent()['database_port'] ?? 3306;
    }

    public function getDatabaseUser(): string
    {
        return $this->getFullContent()['database_user'];
    }

    public function getDatabasePassword(): string
    {
        return $this->getFullContent()['database_password'];
    }

    public function getDatabaseName(): string
    {
        return $this->getFullContent()['database_name'];
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
