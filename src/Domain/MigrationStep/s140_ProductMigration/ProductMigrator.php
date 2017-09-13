<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s140_ProductMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrate the products data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMigrator implements DataMigrator
{
    /** @var ChainedConsole */
    private $console;

    public function __construct(
        ChainedConsole $console
    ) {
        $this->console = $console;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {

    }
}
