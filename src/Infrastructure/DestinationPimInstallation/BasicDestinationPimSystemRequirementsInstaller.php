<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;

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
            new DoctrineDropDatabaseCommand(), $destinationPim->absolutePath(), true
        );
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineCreateDatabaseCommand(), $destinationPim->absolutePath(), true
        );
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineCreateSchemaCommand(), $destinationPim->absolutePath(), true
        );
        $this->destinationPimCommandLauncher->runCommand(
            new DoctrineUpdateSchemaCommand(), $destinationPim->absolutePath(), true
        );
    }
}
