<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfigurator;

class SourcePimConfiguratorFactory
{
    public function createSourcePimConfigurator(FileFetcher $fileFetcher): SourcePimConfigurator
    {
        return new SourcePimConfigurator($fileFetcher);
    }
}
