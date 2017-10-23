<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\Pim\PimParameters;
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

    /** @var FileSystemHelper */
    private $fileSystemHelper;

    public function __construct(ChainedConsole $chainedConsole, FileSystemHelper $fileSystemHelper)
    {
        $this->chainedConsole = $chainedConsole;
        $this->fileSystemHelper = $fileSystemHelper;
    }

    public function install(DestinationPim $pim): void
    {
        $this->updatePimParameterInstallerDataToMinimal($pim);

        $this->chainedConsole->execute(new SymfonyCommand('cache:clear', SymfonyCommand::PROD), $pim);
        $this->chainedConsole->execute(new SymfonyCommand('pim:installer:db', SymfonyCommand::PROD), $pim);
    }

    public function supports(PimConnection $connection): bool
    {
        return $connection instanceof Localhost;
    }

    private function updatePimParameterInstallerDataToMinimal(DestinationPim $pim): void
    {
        $pimParametersFilePath = sprintf('%s/app/config/%s', $pim->absolutePath(), PimParameters::getFileName());
        $pimParametersLines = $this->fileSystemHelper->getFileLines($pimParametersFilePath);

        foreach ($pimParametersLines as $numLine => $pimParametersLine) {
            if (1 === preg_match('/^\s*installer_data:/', $pimParametersLine)) {
                $this->fileSystemHelper->updateLineInFile($pimParametersFilePath, $numLine + 1, '    installer_data: PimInstallerBundle:minimal'.PHP_EOL);

                return;
            }
        }

        throw new \RuntimeException('Unable to find the parameter installer_data in '.$pimParametersFilePath);
    }
}
