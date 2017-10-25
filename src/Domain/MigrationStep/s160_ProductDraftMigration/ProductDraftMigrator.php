<?php

namespace Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftMigrator implements DataMigrator
{
    /** @var MigrationBundleInstaller */
    private $bundleInstaller;

    /** @var ChainedConsole */
    private $console;

    /** @var FileFetcherRegistry */
    private $fileFetcherRegistry;

    /** @var LoggerInterface */
    private $logger;

    /** @var ProductDraftImporter */
    private $productDraftImporter;

    /**
     * ProductDraftMigrator constructor.
     *
     * @param MigrationBundleInstaller $bundleInstaller
     * @param ChainedConsole           $console
     * @param FileFetcherRegistry      $fileFetcherRegistry
     * @param LoggerInterface          $logger
     */
    public function __construct(
        MigrationBundleInstaller $bundleInstaller,
        ChainedConsole $console,
        FileFetcherRegistry $fileFetcherRegistry,
        ProductDraftImporter $productDraftImporter,
        LoggerInterface $logger
    ) {
        $this->bundleInstaller = $bundleInstaller;
        $this->console = $console;
        $this->fileFetcherRegistry = $fileFetcherRegistry;
        $this->productDraftImporter = $productDraftImporter;
        $this->logger = $logger;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $this->bundleInstaller->install($sourcePim);

        $output = $this->console->execute(new SymfonyCommand('transporteo:migration:draft', SymfonyCommand::PROD), $sourcePim);
        $draftFilename = trim($output->getOutput());

        $this->fileFetcherRegistry->fetch($sourcePim->getConnection(), sprintf('/tmp/%s', $draftFilename), true);

        $this->productDraftImporter->import($destinationPim, $draftFilename);
    }

}
