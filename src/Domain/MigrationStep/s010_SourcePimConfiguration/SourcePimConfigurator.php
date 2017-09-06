<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\Pim\AbstractPimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Configurator for the Source Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimConfigurator extends AbstractPimConfigurator implements PimConfigurator
{
    /** @var FileFetcherRegistry */
    private $fileFetcherRegistry;

    public function __construct(FileFetcherRegistry $fileFetcherRegistry)
    {
        $this->fileFetcherRegistry = $fileFetcherRegistry;
    }

    protected function fetch(PimConnection $connection, string $path): string
    {
        return $this->fileFetcherRegistry->fetch($connection, $path, true);
    }
}
