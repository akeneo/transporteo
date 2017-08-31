<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Infrastructure\Command\Command;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Export an entity config to a file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DoctrineMappingConvertCommand implements Command
{
    /** @var string */
    private $entityNamespace;

    /** @var string */
    private $format;

    /** @var string */
    private $mappingFilePath;

    public function __construct(string $entityNamespace, string $format, string $mappingFilePath)
    {
        $this->entityNamespace = $entityNamespace;
        $this->format = $format;
        $this->mappingFilePath = $mappingFilePath;
    }

    public function getCommand(): string
    {
        return sprintf(
            '%s app/console doctrine:mapping:convert --force --filter="%s" %s %s',
            (new PhpExecutableFinder())->find(),
            $this->entityNamespace,
            $this->format,
            $this->mappingFilePath
        );
    }
}
