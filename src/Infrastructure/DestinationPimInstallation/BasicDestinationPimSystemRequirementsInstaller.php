<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;

/**
 * Install Pim System Requirements on local.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BasicDestinationPimSystemRequirementsInstaller implements DestinationPimSystemRequirementsInstaller
{
    /** @var ConsoleHelper */
    private $consoleHelper;

    public function __construct(ConsoleHelper $consoleHelper)
    {
        $this->consoleHelper = $consoleHelper;
    }

    public function install(DestinationPim $pim): void
    {
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:database:drop --force'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:database:create'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:schema:create'));
        $this->consoleHelper->execute($pim, new SymfonyCommand('doctrine:schema:update --force'));
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof Localhost;
    }
}
