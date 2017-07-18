<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

/**
 * Representation of a file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface File
{
    public function getPath(): string;

    public static function getName(): string;
}
