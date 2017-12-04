<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Checks that the version of the destination PIM is supported.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimVersionChecker
{
    const EXACT_MAJOR_VERSION = 2;
    const EXACT_MINOR_VERSION = 0;
    const MINIMUM_PATCH_VERSION = 3;

    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    /**
     * @throws DestinationPimCheckConfigurationException if the version of the destination PIM is not readable or is not at least 2.0.3
     */
    public function check(DestinationPim $pim): void
    {
        $systemInformation = $this->console->execute(new SymfonyCommand('pim:system:information'), $pim)->getOutput();
        $version = [];

        if (1 !== preg_match('~Version\s+\| (?P<full>(?P<major>[0-9]+)\.(?P<minor>[0-9]+)\.(?P<patch>[0-9]+))\s+~i', $systemInformation, $version)) {
            throw new DestinationPimCheckConfigurationException('Failed to read the destination PIM version.');
        }

        if ((int) $version['major'] !== self::EXACT_MAJOR_VERSION || (int) $version['minor'] !== self::EXACT_MINOR_VERSION) {
            throw new DestinationPimCheckConfigurationException(sprintf(
                'The current version of your destination PIM %s is not supported. The version should be %d.%d.x',
                $version['full'],
                self::EXACT_MAJOR_VERSION,
                self::EXACT_MINOR_VERSION
            ));
        }

        if ((int) $version['patch'] < self::MINIMUM_PATCH_VERSION) {
            throw new DestinationPimCheckConfigurationException(sprintf(
                'The current version of your destination PIM %s is not supported. The minimum version of the destination PIM is %d.%d.%d',
                $version['full'],
                self::EXACT_MAJOR_VERSION,
                self::EXACT_MINOR_VERSION,
                self::MINIMUM_PATCH_VERSION
            ));
        }
    }
}
