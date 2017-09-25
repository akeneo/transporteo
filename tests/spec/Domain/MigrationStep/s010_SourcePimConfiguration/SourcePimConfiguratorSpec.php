<?php

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration;

use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\MigrationStep\s010_SourcePimConfiguration\SourcePimConfigurator;
use Akeneo\PimMigration\Domain\Pim\ComposerJson;
use Akeneo\PimMigration\Domain\Pim\ParametersYml;
use Akeneo\PimMigration\Domain\Pim\PimConfiguration;
use Akeneo\PimMigration\Domain\Pim\PimConfigurator;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\PimParameters;
use Akeneo\PimMigration\Domain\Pim\PimServerInformation;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Spec for SourcePimConfigurator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimConfiguratorSpec extends ObjectBehavior
{
    public function let(FileFetcherRegistry $fileFetcherRegistry, LoggerInterface $logger)
    {
        $this->beConstructedWith($fileFetcherRegistry, $logger);

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
        $this->shouldHaveType(SourcePimConfigurator::class);
    }

    public function it_returns_the_good_configuration(
        PimConnection $pimConnection,
        $fileFetcherRegistry
    ) {
        $localComposerJsonPath = ResourcesFileLocator::getStepOneAbsoluteComposerJsonLocalPath();
        $localParameterYmlPath = ResourcesFileLocator::getStepOneAbsoluteParametersYamlLocalPath();
        $localPimParametersPath = ResourcesFileLocator::getStepOneAbsolutePimParametersLocalPath();

        $pimServerInfo = new PimServerInformation($localComposerJsonPath, 'nanou-migration');

        $destinationComposerJsonPath = ResourcesFileLocator::getAbsoluteComposerJsonDestinationPath();
        $destinationParametersYmlPath = ResourcesFileLocator::getAbsoluteParametersYamlDestinationPath();
        $destinationPimParametersPath = ResourcesFileLocator::getAbsolutePimParametersDestinationPath();

        $fileFetcherRegistry->fetch($pimConnection, $localComposerJsonPath, true)->willReturn($destinationComposerJsonPath);
        $fileFetcherRegistry->fetch($pimConnection, $localParameterYmlPath, true)->willReturn($destinationParametersYmlPath);
        $fileFetcherRegistry->fetch($pimConnection, $localPimParametersPath, true)->willReturn($destinationPimParametersPath);

        $sourcePimConfiguration = new PimConfiguration(
            new ComposerJson($destinationComposerJsonPath),
            new ParametersYml($destinationParametersYmlPath),
            new PimParameters($destinationPimParametersPath),
            'nanou-migration'
        );

        $this->configure($pimConnection, $pimServerInfo)->shouldBeASourcePimConfigurationLike($sourcePimConfiguration);
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
