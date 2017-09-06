<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

trait SourcePimConnected
{
    /** @var PimConnection */
    protected $sourcePimConnection;

    public function connectSourcePim(PimConnection $pimConnection): void
    {
        $this->sourcePimConnection = $pimConnection;
    }
}
