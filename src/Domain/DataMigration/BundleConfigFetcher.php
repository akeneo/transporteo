<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
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
    /** @var ChainedConsole */
    private $chainedConsole;

    public function __construct(ChainedConsole $chainedConsole)
    {
        $this->chainedConsole = $chainedConsole;
    }

    public function fetch(Pim $pim, string $bundleName): array
    {
        $commandResult = $this
            ->chainedConsole
            ->execute(new SymfonyCommand(sprintf('debug:config %s', $bundleName)), $pim);

        $header = sprintf('# Current configuration for "%s"%s', $bundleName, PHP_EOL);

        return Yaml::parse(str_replace($header, '', $commandResult->getOutput()));
    }
}
