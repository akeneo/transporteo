<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidInnerVariationTypeException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidMixedVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidProductVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidVariantGroupException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation\MixedVariationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductVariationMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVariationMigratorSpec extends ObjectBehavior
{
    public function let(
        ChainedConsole $console,
        InnerVariationTypeMigrator $innerVariantTypeMigrator,
        VariantGroupMigrator $variantGroupMigrator,
        VariantGroupRepository $variantGroupRepository,
        MixedVariationMigrator $mixedVariationMigrator,
        TableMigrator $tableMigrator,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($console, $innerVariantTypeMigrator, $variantGroupMigrator, $variantGroupRepository, $mixedVariationMigrator, $tableMigrator, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ProductVariationMigrator::class);
    }

    public function it_successfully_migrates_product_variations_when_there_are_only_variant_groups(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $mixedVariationMigrator,
        $variantGroupRepository,
        $tableMigrator
    ) {
        $sourcePim->hasIvb()->willReturn(false);
        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(2);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $variantGroupMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $mixedVariationMigrator->migrate(Argument::any())->shouldNotBeCalled();
        $innerVariantTypeMigrator->migrate(Argument::any())->shouldNotBeCalled();

        $console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();
        $console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_successfully_migrates_product_variations_when_there_is_only_ivb(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $mixedVariationMigrator,
        $variantGroupRepository,
        $tableMigrator
    ) {
        $sourcePim->hasIvb()->willReturn(true);
        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(0);

        $tableMigrator->migrate(Argument::any())->shouldNotBeCalled();
        $variantGroupMigrator->migrate(Argument::any())->shouldNotBeCalled();
        $mixedVariationMigrator->migrate(Argument::any())->shouldNotBeCalled();

        $innerVariantTypeMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();
        $console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_successfully_migrates_product_variations_when_there_are_ivb_and_variant_groups_both(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $mixedVariationMigrator,
        $variantGroupRepository,
        $tableMigrator
    ) {
        $sourcePim->hasIvb()->willReturn(true);
        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(2);

        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_group_attribute')->shouldBeCalled();
        $tableMigrator->migrate($sourcePim, $destinationPim, 'pim_catalog_product_template')->shouldBeCalled();

        $variantGroupMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $mixedVariationMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $innerVariantTypeMigrator->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();
        $console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_throws_an_exception_if_there_are_invalid_product_variations(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $console,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $mixedVariationMigrator,
        $variantGroupRepository
    ) {
        $sourcePim->hasIvb()->willReturn(true);
        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(2);

        $invalidInnerVariationTypeException = new InvalidInnerVariationTypeException();
        $invalidVariantGroupException = new InvalidVariantGroupException(1);
        $invalidMixedVariationException = new InvalidMixedVariationException();

        $mixedVariationMigrator->migrate($sourcePim, $destinationPim)->willThrow($invalidMixedVariationException);
        $variantGroupMigrator->migrate($sourcePim, $destinationPim)->willThrow($invalidVariantGroupException);
        $innerVariantTypeMigrator->migrate($sourcePim, $destinationPim)->willThrow($invalidInnerVariationTypeException);

        $console->execute(new SymfonyCommand('pim:product:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();
        $console->execute(new SymfonyCommand('pim:product-model:index --all', SymfonyCommand::PROD), $destinationPim)->shouldBeCalled();

        $this->shouldThrow(new InvalidProductVariationException([
            $invalidMixedVariationException->getMessage(),
            $invalidVariantGroupException->getMessage(),
            $invalidInnerVariationTypeException->getMessage(),
        ]))
            ->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_does_nothing_if_there_are_no_product_variations(
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $variantGroupRepository,
        $tableMigrator,
        $innerVariantTypeMigrator,
        $variantGroupMigrator,
        $mixedVariationMigrator,
        $logger
    ) {
        $sourcePim->hasIvb()->willReturn(false);
        $logger->info('There is no InnerVariationType to migrate.')->shouldBeCalled();

        $variantGroupRepository->retrieveNumberOfVariantGroups($destinationPim)->willReturn(0);
        $logger->info("There are no variant groups to migrate")->shouldBeCalled();

        $tableMigrator->migrate(Argument::any())->shouldNotBeCalled();
        $variantGroupMigrator->migrate(Argument::any())->shouldNotBeCalled();
        $mixedVariationMigrator->migrate(Argument::any())->shouldNotBeCalled();
        $innerVariantTypeMigrator->migrate(Argument::any())->shouldNotBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
