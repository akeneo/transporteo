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
use Psr\Log\LoggerInterface;

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

    /** @var LoggerInterface */
    private $logger;

    private const REFERENCE_DATA_CONFIG_KEY = 'pim_reference_data';

    public function __construct(
        BundleConfigFetcher $bundleConfigFetcher,
        EntityTableNameFetcher $entityTableNameFetcher,
        EntityMappingChecker $entityMappingChecker,
        MigrationBundleInstaller $migrationBundleInstaller,
        ReferenceDataConfigurator $referenceDataConfigurator,
        LoggerInterface $logger
    ) {
        $this->bundleConfigFetcher = $bundleConfigFetcher;
        $this->entityTableNameFetcher = $entityTableNameFetcher;
        $this->entityMappingChecker = $entityMappingChecker;
        $this->migrationBundleInstaller = $migrationBundleInstaller;
        $this->referenceDataConfigurator = $referenceDataConfigurator;
        $this->logger = $logger;
    }

    /**
     * @throws DataMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->logger->debug('ReferenceDataMigrator: Start migrating');

        try {
            $this->logger->debug('ReferenceDataMigrator: Start fetching the config');
            $referenceDataConfig = $this->bundleConfigFetcher->fetch($sourcePim, 'PimReferenceDataBundle');
            $this->logger->debug('ReferenceDataMigrator: Config fetched');
        } catch (\Exception $exception) {
            throw new ReferenceDataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!isset($referenceDataConfig[self::REFERENCE_DATA_CONFIG_KEY])) {
            return;
        }

        try {
            $this->logger->debug('ReferenceDataMigrator: Start installing the MigrationBundle.');
            $this->migrationBundleInstaller->install($destinationPim);
            $this->logger->debug('ReferenceDataMigrator: MigrationBundle installed.');

            foreach ($referenceDataConfig[self::REFERENCE_DATA_CONFIG_KEY] as $referenceData) {
                $this->logger->debug(sprintf('ReferenceDataMigrator: Fetch table name of %s', $referenceData['class']));
                $referenceDataTableName = $this->entityTableNameFetcher->fetchTableName($sourcePim, $referenceData['class']);
                $this->logger->debug(sprintf('ReferenceDataMigrator: Class %s is related to %s table_name', $referenceData['class'], $referenceDataTableName));

                $referenceDataName = strtolower(substr($referenceData['class'], strrpos($referenceData['class'], '\\') + 1));
                $destinationReferenceDataNamespace = $this
                    ->referenceDataConfigurator
                    ->configure($referenceDataName, $referenceData, $referenceDataTableName, $destinationPim)
                ;

                $this->entityMappingChecker->check($destinationPim, $destinationReferenceDataNamespace);
            }
        } catch (\Exception $exception) {
            throw new ReferenceDataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
