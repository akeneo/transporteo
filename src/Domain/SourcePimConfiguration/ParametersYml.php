<?php
declare(strict_types = 1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\File;

/**
 * Representation of a parameters.yml file
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class ParametersYml implements File
{
    /** @var string */
    private $localPath;

    public function __construct(string $localPath)
    {
        $this->localPath = $localPath;
    }

    public function getPath(): string
    {
        return $this->localPath;
    }

    public static function getName(): string
    {
        return 'parameters.yml';
    }
}
