<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

trait DestinationPimConnected
{
    /** @var PimConnection */
    protected $destinationPimConnection;

    public function connectDestinationPim(PimConnection $pimConnection): void
    {
        $this->destinationPimConnection = $pimConnection;
    }
}
