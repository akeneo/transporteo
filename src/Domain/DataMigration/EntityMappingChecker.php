<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Check if an entity is well mapped.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface EntityMappingChecker
{
    /**
     * @throws EntityMappingException
     */
    public function check(Pim $pim, string $entityClassPath): void;
}
