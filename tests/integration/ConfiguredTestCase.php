<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Able to reach the config of a pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class ConfiguredTestCase extends TestCase
{
    protected function getConfig(string $pimName): array
    {
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'test_config.yml';

        $config = file_get_contents($configPath);

        if (false === $config) {
            throw new \Exception(sprintf('You should configure %s before running test.', $configPath));
        }

        $testConfiguration = Yaml::parse($config);

        if (!isset($testConfiguration['parameters'][$pimName])) {
            throw new \InvalidArgumentException(sprintf('The pim %s is not configured', $pimName));
        }

        return $testConfiguration['parameters'][$pimName];
    }
}
