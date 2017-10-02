<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Escapes a string to use it in as parameter in a MySQL query.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface MysqlEscaper
{
    /**
     * Escapes a string for a given PIM.
     */
    public function escape(string $stringToEscape, Pim $pim): string;
}
