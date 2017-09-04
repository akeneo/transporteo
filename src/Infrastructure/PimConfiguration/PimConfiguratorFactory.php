<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\PimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConfigurator;

class PimConfiguratorFactory
{
    public function createSourcePimConfigurator(FileFetcher $fileFetcher): PimConfigurator
    {
        return new PimConfigurator($fileFetcher);
    }

    public function createDestinationPimConfigurator(FileFetcher $fileFetcher): PimConfigurator
    {
        return new DestinationPimConfigurator($fileFetcher);
    }
}
