<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\DataMigration\BundleConfigFetcher;
use Akeneo\PimMigration\Domain\Pim\AbstractPim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\DebugConfigCommand;
use Symfony\Component\Yaml\Yaml;

/**
 * Fetch the bundle config through a symfony command.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandBundleConfigFetcher implements BundleConfigFetcher
{
    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    public function fetch(AbstractPim $pim, string $bundleName): array
    {
        $commandResult = $this
            ->commandLauncher
            ->runCommand(new DebugConfigCommand($bundleName), $pim->absolutePath(), false);

        $header = sprintf('# Current configuration for "%s"%s', $bundleName, PHP_EOL);

        return Yaml::parse(str_replace($header, '', $commandResult->getOutput()));
    }
}
