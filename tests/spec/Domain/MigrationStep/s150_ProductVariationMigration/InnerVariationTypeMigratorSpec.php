<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidInnerVariationTypeException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationCleaner;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationTypeMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeMigratorSpec extends ObjectBehavior
{
    public function let(
        InnerVariationRetriever $innerVariationRetriever,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator,
        InnerVariationProductMigrator $innerVariationProductMigrator,
        InnerVariationCleaner $innerVariationCleaner,
        LoggerInterface $logger
    )
    {
        $this->beConstructedWith($innerVariationRetriever, $innerVariationFamilyMigrator, $innerVariationProductMigrator, $innerVariationCleaner, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationTypeMigrator::class);
    }

    public function it_successfully_migrates_inner_variation_types(
        $innerVariationRetriever,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstInnerVariationType = new InnerVariationType(
            1, 'ivt_with_two_axes', 10, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_metric']
            ]
        );

        $secondInnerVariationType = new InnerVariationType(
            2, 'ivt_with_one_axe', 11, [['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect']]
        );

        $innerVariationRetriever->retrieveInnerVariationTypes($destinationPim)->willReturn([$firstInnerVariationType, $secondInnerVariationType]);

        $innerVariationFamilyMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationFamilyMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationCleaner->deleteInvalidInnerVariationTypesProducts([], $destinationPim)->shouldBeCalled();
        $innerVariationCleaner->cleanInnerVariationTypes([$firstInnerVariationType, $secondInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_does_not_migrate_ivt_having_more_than_five_axes(
        $innerVariationRetriever,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstInnerVariationType = new InnerVariationType(
            1, 'ivt_with_five_axes', 10, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_3', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_4', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_5', 'attribute_type' => 'pim_catalog_simpleselect'],
            ]
        );

        $invalidInnerVariationType = new InnerVariationType(
            2, 'ivt_with_six_axes', 11, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_3', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_4', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_5', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_6', 'attribute_type' => 'pim_catalog_simpleselect'],
            ]
        );

        $innerVariationRetriever->retrieveInnerVariationTypes($destinationPim)->willReturn([$firstInnerVariationType, $invalidInnerVariationType]);

        $innerVariationFamilyMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationFamilyMigrator->migrate($invalidInnerVariationType, $destinationPim)->shouldNotBeCalled();
        $innerVariationProductMigrator->migrate($invalidInnerVariationType, $destinationPim)->shouldNotBeCalled();

        $innerVariationCleaner->deleteInvalidInnerVariationTypesProducts([$invalidInnerVariationType], $destinationPim)->shouldBeCalled();
        $innerVariationCleaner->cleanInnerVariationTypes([$firstInnerVariationType, $invalidInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->shouldThrow(new InvalidInnerVariationTypeException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_does_not_migrate_ivt_having_an_invalid_axes(
        $innerVariationRetriever,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstInnerVariationType = new InnerVariationType(
            1, 'valid_ivt', 10, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_2', 'attribute_type' => 'pim_reference_data_simpleselect'],
                ['code' => 'axe_3', 'attribute_type' => 'pim_catalog_metric'],
                ['code' => 'axe_4', 'attribute_type' => 'pim_catalog_boolean'],
            ]
        );

        $invalidInnerVariationType = new InnerVariationType(
            2, 'invalid_ivt', 11, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'invalid_axe', 'attribute_type' => 'pim_catalog_identifier'],
            ]
        );

        $innerVariationRetriever->retrieveInnerVariationTypes($destinationPim)->willReturn([$firstInnerVariationType, $invalidInnerVariationType]);

        $innerVariationFamilyMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationFamilyMigrator->migrate($invalidInnerVariationType, $destinationPim)->shouldNotBeCalled();
        $innerVariationProductMigrator->migrate($invalidInnerVariationType, $destinationPim)->shouldNotBeCalled();

        $innerVariationCleaner->deleteInvalidInnerVariationTypesProducts([$invalidInnerVariationType], $destinationPim)->shouldBeCalled();
        $innerVariationCleaner->cleanInnerVariationTypes([$firstInnerVariationType, $invalidInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->shouldThrow(new InvalidInnerVariationTypeException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    public function it_continues_to_migrate_if_an_exception_is_thrown(
        $innerVariationRetriever,
        $innerVariationFamilyMigrator,
        $innerVariationProductMigrator,
        $innerVariationCleaner,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    )
    {
        $firstInnerVariationType = new InnerVariationType(
            1, 'ivt_with_two_axes', 10, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_metric']
            ]
        );

        $secondInnerVariationType = new InnerVariationType(
            2, 'ivt_with_one_axe', 11, [['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect']]
        );

        $innerVariationRetriever->retrieveInnerVariationTypes($destinationPim)->willReturn([$firstInnerVariationType, $secondInnerVariationType]);

        $innerVariationFamilyMigrator->migrate($firstInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator
            ->migrate($firstInnerVariationType, $destinationPim)
            ->willThrow(new \Exception());

        $innerVariationFamilyMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();
        $innerVariationProductMigrator->migrate($secondInnerVariationType, $destinationPim)->shouldBeCalled();

        $innerVariationCleaner->deleteInvalidInnerVariationTypesProducts([], $destinationPim)->shouldBeCalled();
        $innerVariationCleaner->cleanInnerVariationTypes([$firstInnerVariationType, $secondInnerVariationType], $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }
}
