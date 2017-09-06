<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\Pim\AbstractPimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConfigurator;

/**
 * Configurator for a destination PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimConfigurator extends AbstractPimConfigurator implements PimConfigurator
{
    /** @var FileFetcherRegistry */
    private $fileFetcherRegistry;

    public function __construct(FileFetcherRegistry $fileFetcherRegistry)
    {
        $this->fileFetcherRegistry = $fileFetcherRegistry;
    }

    protected function fetch(string $path): string
    {
        return $this->fileFetcherRegistry->fetchDestination($path, false);
    }
}
