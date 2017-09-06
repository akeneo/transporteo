<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Symfony\Component\Yaml\Yaml;

/**
 * Fetch the current config of a given bundle in a PIM.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BundleConfigFetcher
{
    /** @var ConsoleHelper */
    private $consoleHelper;

    public function __construct(ConsoleHelper $consoleHelper)
    {
        $this->consoleHelper = $consoleHelper;
    }

    public function fetch(Pim $pim, string $bundleName): array
    {
        $commandResult = $this
            ->consoleHelper
            ->execute($pim, new SymfonyCommand(sprintf('debug:config %s', $bundleName)));

        $header = sprintf('# Current configuration for "%s"%s', $bundleName, PHP_EOL);

        return Yaml::parse(str_replace($header, '', $commandResult->getOutput()));
    }
}
