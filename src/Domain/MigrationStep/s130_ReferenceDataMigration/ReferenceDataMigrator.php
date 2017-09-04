<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s130_ReferenceDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\BundleConfigFetcher;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\EntityMappingChecker;
use Akeneo\PimMigration\Domain\DataMigration\EntityTableNameFetcher;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Migrator for reference data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataMigrator implements DataMigrator
{
    /** @var BundleConfigFetcher */
    private $bundleConfigFetcher;

    /** @var EntityTableNameFetcher */
    private $entityTableNameFetcher;

    /** @var EntityMappingChecker */
    private $entityMappingChecker;

    /** @var MigrationBundleInstaller */
    private $migrationBundleInstaller;

    /** @var ReferenceDataConfigurator */
    private $referenceDataConfigurator;

    private const REFERENCE_DATA_CONFIG_KEY = 'pim_reference_data';

    public function __construct(
        BundleConfigFetcher $bundleConfigFetcher,
        EntityTableNameFetcher $entityTableNameFetcher,
        EntityMappingChecker $entityMappingChecker,
        MigrationBundleInstaller $migrationBundleInstaller,
        ReferenceDataConfigurator $referenceDataConfigurator
    ) {
        $this->bundleConfigFetcher = $bundleConfigFetcher;
        $this->entityTableNameFetcher = $entityTableNameFetcher;
        $this->entityMappingChecker = $entityMappingChecker;
        $this->migrationBundleInstaller = $migrationBundleInstaller;
        $this->referenceDataConfigurator = $referenceDataConfigurator;
    }

    /**
     * @throws DataMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $referenceDataConfig = $this->bundleConfigFetcher->fetch($sourcePim, 'PimReferenceDataBundle');
        } catch (\Exception $exception) {
            throw new ReferenceDataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!isset($referenceDataConfig[self::REFERENCE_DATA_CONFIG_KEY])) {
            return;
        }

        try {
            $this->migrationBundleInstaller->install($destinationPim);

            foreach ($referenceDataConfig[self::REFERENCE_DATA_CONFIG_KEY] as $referenceData) {
                $referenceDataTableName = $this->entityTableNameFetcher->fetchTableName($sourcePim, $referenceData['class']);

                $destinationReferenceDataNamespace = $this
                    ->referenceDataConfigurator
                    ->configure($referenceData, $referenceDataTableName, $destinationPim)
                ;
                $this->entityMappingChecker->check($destinationPim, $destinationReferenceDataNamespace);
            }
        } catch (\Exception $exception) {
            throw new ReferenceDataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
