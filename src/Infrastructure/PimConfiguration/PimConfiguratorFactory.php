<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\PimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\PimConfiguration\PimConfigurator;

class PimConfiguratorFactory
{
    public function createPimConfigurator(FileFetcher $fileFetcher): PimConfigurator
    {
        return new PimConfigurator($fileFetcher);
    }
}
