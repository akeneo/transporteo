<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimParameters;
use Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\BasicDestinationPimSystemRequirementsInstaller;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BasicDestinationPimSystemRequirementsInstallerSpec extends \PhpSpec\ObjectBehavior
{
    public function let(ChainedConsole $chainedConsole, FileSystemHelper $fileSystemHelper)
    {
        $this->beConstructedWith($chainedConsole, $fileSystemHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(BasicDestinationPimSystemRequirementsInstaller::class);
    }

    public function it_installs_the_destination_system_requirements_of_a_CE_PIM(DestinationPim $pim, $chainedConsole, $fileSystemHelper)
    {
        $pim->absolutePath()->willReturn('/pim/path');

        $pimParametersFilePath = '/pim/path/app/config/' . PimParameters::getFileName();
        $fileSystemHelper->getFileLines($pimParametersFilePath)->willReturn([
            'parameters:',
            '    installer_data:       PimInstallerBundle:icecat_demo_dev',
            ''
        ]);

        $pim->isEnterpriseEdition()->willReturn(false);

        $fileSystemHelper->updateLineInFile($pimParametersFilePath, 2, "    installer_data: PimInstallerBundle:minimal\n")->shouldBeCalled();

        $chainedConsole->execute(new SymfonyCommand('cache:clear', SymfonyCommand::PROD), $pim)->shouldBeCalled();
        $chainedConsole->execute(new SymfonyCommand('pim:installer:db', SymfonyCommand::PROD), $pim)->shouldBeCalled();

        $this->install($pim);
    }

    public function it_installs_the_destination_system_requirements_of_a_EE_PIM(DestinationPim $pim, $chainedConsole, $fileSystemHelper)
    {
        $pim->absolutePath()->willReturn('/pim/path');

        $pimParametersFilePath = '/pim/path/app/config/' . PimParameters::getFileName();
        $fileSystemHelper->getFileLines($pimParametersFilePath)->willReturn([
            'parameters:',
            '    installer_data:       PimEnterpriseInstallerBundle:icecat_demo_dev',
            ''
        ]);

        $pim->isEnterpriseEdition()->willReturn(true);

        $fileSystemHelper->updateLineInFile($pimParametersFilePath, 2, "    installer_data: PimEnterpriseInstallerBundle:minimal\n")->shouldBeCalled();

        $chainedConsole->execute(new SymfonyCommand('cache:clear', SymfonyCommand::PROD), $pim)->shouldBeCalled();
        $chainedConsole->execute(new SymfonyCommand('pim:installer:db', SymfonyCommand::PROD), $pim)->shouldBeCalled();

        $this->install($pim);
    }

    public function it_throws_an_exception_if_the_config_line_to_update_has_not_been_found(DestinationPim $pim, $fileSystemHelper)
    {
        $pim->absolutePath()->willReturn('/pim/path');

        $pimParametersFilePath = '/pim/path/app/config/' . PimParameters::getFileName();
        $fileSystemHelper->getFileLines($pimParametersFilePath)->willReturn([
            'parameters:',
            '    session_handler:      ~',
            ''
        ]);

        $this->shouldThrow(new \RuntimeException('Unable to find the parameter installer_data in ' . $pimParametersFilePath))
            ->during('install', [$pim]);
    }
}
