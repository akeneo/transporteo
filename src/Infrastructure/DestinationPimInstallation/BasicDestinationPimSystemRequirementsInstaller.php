<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Domain\Command\CommandLauncher;

/**
 * Install Pim System Requirements on local.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BasicDestinationPimSystemRequirementsInstaller implements DestinationPimSystemRequirementsInstaller
{
    /** @var CommandLauncher */
    private $destinationPimCommandLauncher;

    public function __construct(CommandLauncher $destinationPimCommandLauncher)
    {
        $this->destinationPimCommandLauncher = $destinationPimCommandLauncher;
    }

    public function install(DestinationPim $destinationPim): void
    {
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineDropDatabaseCommand(), $destinationPim->getPath(), true
        );
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineCreateDatabaseCommand(), $destinationPim->getPath(), true
        );
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineCreateSchemaCommand(), $destinationPim->getPath(), true
        );
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineUpdateSchemaCommand(), $destinationPim->getPath(), true
        );
    }
}
