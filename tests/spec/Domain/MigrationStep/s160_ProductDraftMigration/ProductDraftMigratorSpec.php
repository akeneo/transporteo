<?php

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration\MigrationBundleInstaller;
use Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration\ProductDraftImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration\ProductDraftMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class ProductDraftMigratorSpec extends ObjectBehavior
{
    function let(
        MigrationBundleInstaller $bundleInstaller,
        ChainedConsole $console,
        FileFetcherRegistry $fileFetcherRegistry,
        ProductDraftImporter $productDraftImporter,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($bundleInstaller, $console, $fileFetcherRegistry, $productDraftImporter, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductDraftMigrator::class);
    }

    function it_migrate_drafts_and_proposals(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        PimConnection $connection,
        $bundleInstaller,
        $console,
        $fileFetcherRegistry,
        $productDraftImporter
    ) {
        $bundleInstaller->install($sourcePim)->shouldBeCalled();

        $console->execute(new SymfonyCommand(
            'transporteo:migration:draft',
            SymfonyCommand::PROD), $sourcePim
        )->shouldBeCalled();

        $sourcePim->getConnection()->willReturn($connection);

        $fileFetcherRegistry->fetch($connection, Argument::any(), true)->willReturn(Argument::any());
        $productDraftImporter->import($destinationPim, Argument::any());

        $this->migrate($sourcePim, $destinationPim);
    }
}
