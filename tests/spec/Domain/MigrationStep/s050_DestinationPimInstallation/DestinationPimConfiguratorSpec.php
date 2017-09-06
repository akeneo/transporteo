<?php

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimConfigurator;
use Akeneo\PimMigration\Domain\Pim\ComposerJson;
use Akeneo\PimMigration\Domain\Pim\ParametersYml;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimParameters;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Spec for DestinationPimConfigurator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimConfiguratorSpec extends ObjectBehavior
{
    public function let(FileFetcherRegistry $fileFetcherRegistry)
    {
        $this->beConstructedWith($fileFetcherRegistry);

        $fs = new Filesystem();
        $fs->copy(
            ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath(),
            ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath()
        );
        $fs->copy(
            ResourcesFileLocator::getStepOneAbsoluteParametersYamlLocalPath(),
            ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath()
        );
        $fs->copy(
            ResourcesFileLocator::getStepOneAbsolutePimParametersLocalPath(),
            ResourcesFileLocator::getAbsolutePimParametersDestinationPath()
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DestinationPimConfigurator::class);
    }

    public function it_returns_the_good_configuration($fileFetcherRegistry)
    {
        $localComposerJsonPath = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $localParameterYmlPath = ResourcesFileLocator::getStepOneAbsoluteParametersYamlLocalPath();
        $localPimParametersPath = ResourcesFileLocator::getStepOneAbsolutePimParametersLocalPath();

        $pimServerInfo = new PimServerInformation($localComposerJsonPath, 'nanou-migration');

        $destinationComposerJsonPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();
        $destinationParametersYmlPath = ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath();
        $destinationPimParametersPath = ResourcesFileLocator::getAbsolutePimParametersDestinationPath();

        $fileFetcherRegistry->fetchDestination($localComposerJsonPath, false)->willReturn($destinationComposerJsonPath);
        $fileFetcherRegistry->fetchDestination($localParameterYmlPath, false)->willReturn($destinationParametersYmlPath);
        $fileFetcherRegistry->fetchDestination($localPimParametersPath, false)->willReturn($destinationPimParametersPath);

        $sourcePimConfiguration = new PimConfiguration(
            new ComposerJson($destinationComposerJsonPath),
            new ParametersYml($destinationParametersYmlPath),
            new PimParameters($destinationPimParametersPath),
            'nanou-migration'
        );

        $this->configure($pimServerInfo)->shouldBeASourcePimConfigurationLike($sourcePimConfiguration);
    }

    public function getMatchers()
    {
        return [
            'beASourcePimConfigurationLike' => function (PimConfiguration $result, PimConfiguration $expected) {
                return (
                    $result->getComposerJson()->getPath() === $expected->getComposerJson()->getPath() &&
                    $result->getParametersYml()->getPath() === $expected->getParametersYml()->getPath() &&
                    $result->getPimParameters()->getPath() === $expected->getPimParameters()->getPath()
                );
            }
        ];
    }

    public function letGo()
    {
        $fs = new Filesystem();
        $fs->remove(ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath());
        $fs->remove(ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath());
        $fs->remove(ResourcesFileLocator::getAbsolutePimParametersDestinationPath());
    }
}
