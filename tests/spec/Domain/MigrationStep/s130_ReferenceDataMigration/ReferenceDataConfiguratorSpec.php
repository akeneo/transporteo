<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration\ReferenceDataConfigurator;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use resources\Akeneo\PimMigration\ResourcesFileLocator;

/**
 * Spec for Reference Data Configurator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataConfiguratorSpec extends ObjectBehavior
{
    public function let(FileSystemHelper $fileSystem, LoggerInterface $logger)
    {
        $this->beConstructedWith($fileSystem, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataConfigurator::class);
    }

    public function it_configures_an_usual_reference_data(
        Pim $pim,
        $fileSystem
    ) {
        $destinationPimPath = '/a-path';
        $pim->absolutePath()->willReturn($destinationPimPath);

        $referenceDataMigrationConfigDir = sprintf(
            '%s%sDomain%sMigrationStep%ss130_ReferenceDataMigration%sconfig',
            ResourcesFileLocator::getSrcDir(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $sampleOrmPath = sprintf(
            '%s%sSample.orm.yml',
            $referenceDataMigrationConfigDir,
            DIRECTORY_SEPARATOR
        );

        $referenceDataConfig = [
            'THE_CLASS_NAME' => [
                'repositoryClass' => 'Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository',
                'type' => 'entity',
                'table' => 'the_table_name',
                'changeTrackingPolicy' => 'DEFERRED_EXPLICIT',
                'fields' => [
                    'id' => [
                        'type' => 'integer',
                        'id' => true,
                        'generator' => [
                            'strategy' => 'AUTO'
                        ]
                    ],
                    'code' => [
                        'type' => 'string',
                        'length' => 255,
                        'unique' => true,
                    ]
                ],
                'lifecycleCallbacks' => []
            ]
        ];

        $fileSystem->getYamlContent($sampleOrmPath)->willReturn($referenceDataConfig);


        $finalValue = [
            'Akeneo\Bundle\MigrationBundle\Entity\Fabric' => [
                'repositoryClass' => 'Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository',
                'type' => 'entity',
                'table' => 'acme_reference_data_fabric',
                'changeTrackingPolicy' => 'DEFERRED_EXPLICIT',
                'fields' => [
                    'id' => [
                        'type' => 'integer',
                        'id' => true,
                        'generator' => [
                            'strategy' => 'AUTO'
                        ]
                    ],
                    'code' => [
                        'type' => 'string',
                        'length' => 255,
                        'unique' => true
                    ]
                ],
                'lifecycleCallbacks' => []
            ]
        ];

        $destinationEntityDefinitionPath = sprintf(
            '%s%ssrc%sAkeneo%sBundle%sMigrationBundle%sResources%sconfig%sdoctrine%s%s.orm.yml',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            'Fabric'
        );

        $fileSystem->dumpYamlInFile($destinationEntityDefinitionPath, $finalValue)->shouldBeCalled();

        $sampleEntityPath = sprintf('%s%sEntity.php', $referenceDataMigrationConfigDir, DIRECTORY_SEPARATOR);

        $newClassPath = 'Akeneo\Bundle\MigrationBundle\Entity\Fabric';

        $destinationEntityPath = sprintf(
            '%s%ssrc%s%s.php',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            str_replace('\\', DIRECTORY_SEPARATOR, $newClassPath)
        );

        $fileSystem->getFileLine($destinationEntityPath, 15)->willReturn('class THE_CLASS_NAME extends AbstractReferenceData implements ReferenceDataInterface');

        $fileSystem->copyFile($sampleEntityPath, $destinationEntityPath, true)->shouldBeCalled();

        $fileSystem->updateLineInFile($destinationEntityPath, 15, 'class Fabric extends AbstractReferenceData implements ReferenceDataInterface')->shouldBeCalled();

        $fabricClassPath = 'Acme\Bundle\AppBundle\Entity\Fabric';
        $fabric = [
            'class' => $fabricClassPath,
            'type'  => 'multi'
        ];

        $configFilePath = sprintf(
            '%s%sapp%sconfig%sconfig.yml',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $config = [
            'pim_reference_data' => null,
            'akeneo_storage_utils' => null,
            'akeneo_elasticsearch' => [
                'index_name' => '%index_name%',
                'hosts' => '%index_hosts%',
                'configuration_files' => '%elasticsearch_index_configuration_files%'
            ]
        ];

        $fileSystem->getYamlContent($configFilePath)->willReturn($config);

        $configResult = [
            'pim_reference_data' => [
                0 => [
                    'class' => 'Akeneo\Bundle\MigrationBundle\Entity\Fabric',
                    'type' => 'multi',
                ]
            ],
            'akeneo_storage_utils' => null,
            'akeneo_elasticsearch' => [
                'index_name' => '%index_name%',
                'hosts' => '%index_hosts%',
                'configuration_files' => '%elasticsearch_index_configuration_files%',
            ]
        ];

        $fileSystem->dumpYamlInFile($configFilePath, $configResult)->shouldBeCalled();

        $this->configure($fabric, 'acme_reference_data_fabric', $pim)->shouldReturn('Akeneo\Bundle\MigrationBundle\Entity\Fabric');
    }

    public function it_does_nothing_for_an_asset(Pim $pim)
    {
        $assetClass = 'PimEnterprise\Component\ProductAsset\Model\Asset';

        $asset = [
            'class' => $assetClass,
            'type' =>  'multi'
        ];

        $this->configure($asset, 'pimee_product_asset_asset', $pim)->shouldReturn($assetClass);
    }
}
