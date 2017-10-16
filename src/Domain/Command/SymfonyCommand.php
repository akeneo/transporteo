<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * SymfonyCommand like debug:config or container:debug.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SymfonyCommand extends AbstractCommand implements Command
{
    const PROD = 'prod';
    const DEV = 'dev';

    /** @var string */
    private $env;

    public function __construct(string $command, string $env = self::DEV)
    {
        parent::__construct($command);
        $this->env = $env;
    }

    public function getEnv(): string
    {
        return $this->env;
    }
}
