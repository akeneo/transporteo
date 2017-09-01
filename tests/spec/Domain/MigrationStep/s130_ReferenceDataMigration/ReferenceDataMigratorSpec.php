<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\BundleConfigFetcher;
use Akeneo\PimMigration\Domain\DataMigration\EntityMappingChecker;
use Akeneo\PimMigration\Domain\DataMigration\EntityTableNameFetcher;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration\MigrationBundleInstaller;
use Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration\ReferenceDataConfigurator;
use Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration\ReferenceDataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ReferenceDataMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataMigratorSpec extends ObjectBehavior
{
    public function let(
        BundleConfigFetcher $bundleConfigFetcher,
        EntityTableNameFetcher $entityTableNameFetcher,
        EntityMappingChecker $entityMappingChecker,
        MigrationBundleInstaller $migrationBundleInstaller,
        ReferenceDataConfigurator $referenceDataConfigurator
    ) {
        $this->beConstructedWith(
            $bundleConfigFetcher,
            $entityTableNameFetcher,
            $entityMappingChecker,
            $migrationBundleInstaller,
            $referenceDataConfigurator
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataMigrator::class);
    }

    public function it_migrates(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $bundleConfigFetcher,
        $entityMappingChecker,
        $migrationBundleInstaller,
        $referenceDataConfigurator,
        $entityTableNameFetcher
    ) {
        $fabricClassPath = 'Acme\Bundle\AppBundle\Entity\Fabric';

        $fabric = [
            'class' => $fabricClassPath,
            'type'  => 'multi'
        ];

        $result = ['pim_reference_data' => [$fabric]];

        $bundleConfigFetcher->fetch($sourcePim, 'PimReferenceDataBundle')->willReturn($result);

        $migrationBundleInstaller->install($destinationPim)->shouldBeCalled();

        $entityTableNameFetcher->fetchTableName($sourcePim, $fabricClassPath)->willReturn('a_table_name');

        $newClassNamespace = 'Akeneo\\Bundle\\MigrationBundle\\Entity\\Fabric';
        $referenceDataConfigurator->configure($fabric, 'a_table_name', $destinationPim)->willReturn($newClassNamespace);

        $entityMappingChecker->check($destinationPim, $newClassNamespace)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
