<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
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
    /** @var ChainedConsole */
    private $chainedConsole;

    public function __construct(ChainedConsole $chainedConsole)
    {
        $this->chainedConsole = $chainedConsole;
    }

    public function install(DestinationPim $pim): void
    {
        $this->chainedConsole->execute(new SymfonyCommand('doctrine:database:drop --force', SymfonyCommand::PROD), $pim);
        $this->chainedConsole->execute(new SymfonyCommand('doctrine:database:create',SymfonyCommand::PROD), $pim);
        $this->chainedConsole->execute(new SymfonyCommand('doctrine:schema:create', SymfonyCommand::PROD), $pim);
        $this->chainedConsole->execute(new SymfonyCommand('doctrine:schema:update --force',SymfonyCommand::PROD), $pim);
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof Localhost;
    }
}
