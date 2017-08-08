<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\CommandLauncher;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Check if the destination PIM is ready to use.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimConfigurationChecker
{
    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    public function check(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        if ($sourcePim->isEnterpriseEdition() !== $destinationPim->isEnterpriseEdition()) {
            throw new IncompatiblePimException(
                sprintf(
                    'The source PIM is %s whereas the destination PIM is %s',
                    $sourcePim->isEnterpriseEdition() ? 'an Enterprise Edition' : 'not an Enterprise Edition',
                    $destinationPim->isEnterpriseEdition() ? 'an Enterprise Edition' : 'not an Enterprise Edition'
                )
            );
        }

        $this->commandLauncher->runCommand(new CheckRequirementsCommand(), $destinationPim);
    }
}
