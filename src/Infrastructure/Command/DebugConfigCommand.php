<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Command;

/**
 * Debug Config Command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DebugConfigCommand implements Command
{
    /** @var string */
    private $bundleName;

    public function __construct(string $bundleName)
    {
        $this->bundleName = $bundleName;
    }

    public function getCommand(): string
    {
        return sprintf(
            'php app/console debug:config %s',
            $this->bundleName
        );
    }
}
