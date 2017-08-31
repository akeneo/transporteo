<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\FileSystem;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Domain\ReferenceDataMigration\ReferenceDataConfigurator;
use PhpSpec\ObjectBehavior;
use resources\Akeneo\PimMigration\ResourcesFileLocator;
use \Symfony\Component\Filesystem\Filesystem as SfFileSystem;

/**
 * Spec for Reference Data Configurator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataConfiguratorSpec extends ObjectBehavior
{
    public function let(FileSystem $fileSystem)
    {
        $this->beConstructedWith($fileSystem);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataConfigurator::class);
    }

    public function it_configures(
        AbstractPim $pim,
        SfFileSystem $sfFileSystem,
        $fileSystem
    ) {
        $destinationPimPath = '/a-path';
        $pim->getPath()->willReturn($destinationPimPath);

        $referenceDataMigrationConfigDir = sprintf(
            '%s%sDomain%sReferenceDataMigration%sconfig',
            ResourcesFileLocator::getSrcDir(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        $sampleOrmPath = sprintf(
            '%s%sSample.orm.yml',
            $referenceDataMigrationConfigDir,
            DIRECTORY_SEPARATOR
        );

        $referenceDataConfig = <<<YAML
THE_CLASS_NAME:
    repositoryClass: Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository
    type: entity
    table: the_table_name
    changeTrackingPolicy: DEFERRED_EXPLICIT
    fields:
        id:
            type: integer
            id: true
            generator:
                strategy: AUTO
        code:
            type: string
            length: 255
            unique: true
    lifecycleCallbacks: {  }

YAML;

        $fileSystem->getFileContent($sampleOrmPath)->willReturn($referenceDataConfig);

        $finalValue = <<<YAML
Akeneo\Bundle\MigrationBundle\Entity\Fabric:
    repositoryClass: Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository
    type: entity
    table: acme_reference_data_fabric
    changeTrackingPolicy: DEFERRED_EXPLICIT
    fields: { id: { type: integer, id: true, generator: { strategy: AUTO } }, code: { type: string, length: 255, unique: true } }
    lifecycleCallbacks: {  }

YAML;

        $fileSystem->getFileSystem()->willReturn($sfFileSystem);

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

        $sfFileSystem->dumpFile($destinationEntityDefinitionPath, $finalValue)->shouldBeCalled();

        $sampleEntityPath = sprintf('%s%sEntity.php', $referenceDataMigrationConfigDir, DIRECTORY_SEPARATOR);

        $newClassPath = 'Akeneo\\Bundle\\MigrationBundle\\Entity\\Fabric';

        $destinationEntityPath = sprintf(
            '%s%ssrc%s%s.php',
            $destinationPimPath,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            str_replace('\\', DIRECTORY_SEPARATOR, $newClassPath)
        );

        $fileSystem->getFileLine($destinationEntityPath, 14)->willReturn('class THE_CLASS_NAME extends AbstractReferenceData implements ReferenceDataInterface');

        $sfFileSystem->copy($sampleEntityPath, $destinationEntityPath)->shouldBeCalled();

        $fileSystem->updateLineInFile($destinationEntityPath, 14, 'class Fabric extends AbstractReferenceData implements ReferenceDataInterface')->shouldBeCalled();

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

        $config = <<<YAML
pim_reference_data: ~
akeneo_storage_utils: null
akeneo_elasticsearch:
    index_name: '%index_name%'
    hosts: '%index_hosts%'
    configuration_files: '%elasticsearch_index_configuration_files%'
YAML;

        $fileSystem->getFileContent($configFilePath)->willReturn($config);

        $configResult = <<<YAML
pim_reference_data:
    -
        class: Akeneo\Bundle\MigrationBundle\Entity\Fabric
        type: multi
akeneo_storage_utils: null
akeneo_elasticsearch:
    index_name: '%index_name%'
    hosts: '%index_hosts%'
    configuration_files: '%elasticsearch_index_configuration_files%'

YAML;

        $sfFileSystem->dumpFile($configFilePath, $configResult)->shouldBeCalled();

        $this->configure($fabric, 'acme_reference_data_fabric', $pim)->shouldReturn('Akeneo\\Bundle\\MigrationBundle\\Entity\\Fabric');
    }
}
