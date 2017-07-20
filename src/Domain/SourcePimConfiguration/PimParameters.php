<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Representation of a pim_parameters.yml file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimParameters implements File
{
    /** @var string */
    private $localPath;

    /** @var array */
    private $fullContent;

    public function __construct(string $localPath)
    {
        $this->localPath = $localPath;
        if (file_exists($localPath)) {
            $this->fullContent = Yaml::parse(file_get_contents($localPath))['parameters'];
        }
    }

    public function getPath(): string
    {
        return $this->localPath;
    }

    public function getMongoDbInformation(): ?string
    {
        return $this->fullContent['mongodb_server'] ?? null;
    }

    public function getMongoDbDatabase(): ?string
    {
        return $this->fullContent['mongodb_database'] ?? null;
    }

    public static function getFileName(): string
    {
        return 'pim_parameters.yml';
    }
}
