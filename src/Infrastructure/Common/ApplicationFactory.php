<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Common;

use Symfony\Component\Console\Application;

/**
 * Symfony application factory.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class ApplicationFactory
{
    public static function create(bool $autoExit = true, string $env = 'prod'): Application
    {
        $application = new Application('Akeneo PIM Migration Tool', '1.0.0');

        return $application;
    }
}
