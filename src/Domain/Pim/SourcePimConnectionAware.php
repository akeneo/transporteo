<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

/**
 * Defining that a class should know the destinationPimConnection.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface SourcePimConnectionAware
{
    public function connectSourcePim(PimConnection $pimConnection): void;
}
