<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\Api\UpdateFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationFamilyMigratorSpec extends ObjectBehavior
{
    function let(
        InnerVariationTypeRepository $innerVariationTypeRepository,
        FamilyVariantImporter $familyVariantImporter,
        ChainedConsole $console,
        LoggerInterface $logger,
        FamilyVariantRepository $familyVariantRepository,
        FamilyRepository $familyRepository
    )
    {
        $this->beConstructedWith(
            $innerVariationTypeRepository,
            $familyVariantImporter,
            $console,
            $logger,
            $familyVariantRepository,
            $familyRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationFamilyMigrator::class);
    }

    function it_successfully_migrates_families(
        $innerVariationTypeRepository,
        $familyRepository,
        InnerVariationType $innerVariationType,
        DestinationPim $pim,
        FamilyVariantRepository $familyVariantRepository,
        $console
    )
    {
        $innerVariationFamily = new Family(1, 'inner_variation_family', [
            'code' => 'inner_variation_family',
            'attributes' => ['attribute_1', 'attribute_2', 'variation_parent_product'],
            'attribute_requirements' => [
                'ecommerce' => ['attribute_1', 'attribute_2', 'variation_parent_product'],
                'mobile' => ['attribute_1']
            ],
            'labels' => [
                'en_US' => 'inner_variation_family label US',
                'fr_FR' => 'inner_variation_family label FR'
            ]
        ]);

        $innerVariationType->getVariationFamily()->willReturn($innerVariationFamily);

        $firstParentFamily = new Family(1, 'first_parent_family', [
            'code' => 'first_parent_family',
            'attributes' => ['attribute_1', 'attribute_3'],
            'attribute_requirements' => [
                'ecommerce' => ['attribute_1', 'attribute_3'],
                'mobile' => []
            ],
            'labels' => [
                'en_US' => 'First parent label US',
                'fr_FR' => 'First parent label FR'
            ]
        ]);
        $secondParentFamily = new Family(1, 'second_parent_family', [
            'code' => 'second_parent_family',
            'attributes' => ['attribute_2', 'attribute_3'],
            'attribute_requirements' => [
                'ecommerce' => ['attribute_2'],
                'mobile' => ['attribute_2']
            ],
            'labels' => [
                'en_US' => 'Second parent label US',
                'fr_FR' => 'Second parent label FR'
            ]
        ]);

        $familyRepository->findAllByInnerVariationType($innerVariationType, $pim)->willReturn(new \ArrayObject([$firstParentFamily, $secondParentFamily]));

        $console->execute(new UpdateFamilyCommand([
            'code' => 'first_parent_family',
            'attributes' => ['attribute_1', 'attribute_3', 'attribute_2'],
            'attribute_requirements' => [
                'ecommerce' => ['attribute_1', 'attribute_3', 'attribute_2'],
                'mobile' => ['attribute_1']
            ],
            'labels' => [
                'en_US' => 'First parent label US',
                'fr_FR' => 'First parent label FR'
            ]
        ]), $pim)->shouldBeCalled();
        $console->execute(new UpdateFamilyCommand([
            'code' => 'second_parent_family',
            'attributes' => ['attribute_2', 'attribute_3', 'attribute_1'],
            'attribute_requirements' => [
                'ecommerce' => ['attribute_2', 'attribute_1'],
                'mobile' => ['attribute_2', 'attribute_1']
            ],
            'labels' => [
                'en_US' => 'Second parent label US',
                'fr_FR' => 'Second parent label FR'
            ]
        ]), $pim)->shouldBeCalled();

        $innerVariationType->getAxes()->willReturn([
            ['code' => 'axis_1', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axis_2', 'attribute_type' => 'pim_catalog_metric']
        ]);

        $innerVariationTypeRepository->getLabel($innerVariationType, 'en_US', $pim)->willReturn('IVT US');
        $innerVariationTypeRepository->getLabel($innerVariationType, 'fr_FR', $pim)->willReturn('IVT FR');

        $firstFamilyVariant = new FamilyVariant(
            null,
            'first_parent_family',
            'first_parent_family',
            ['axis_1', 'axis_2'],
            [],
            ['attribute_1', 'attribute_2'],
            [],
            [
                'en_US' => 'First parent label US IVT US',
                'fr_FR' => 'First parent label FR IVT FR',
            ]
        );

        $secondFamilyVariant = new FamilyVariant(
            null,
            'second_parent_family',
            'second_parent_family',
            ['axis_1', 'axis_2'],
            [],
            ['attribute_1', 'attribute_2'],
            [],
            [
                'en_US' => 'Second parent label US IVT US',
                'fr_FR' => 'Second parent label FR IVT FR',
            ]
        );

        $familyVariantRepository->persist($firstFamilyVariant, $pim);
        $familyVariantRepository->persist($secondFamilyVariant, $pim);

        $this->migrate($innerVariationType, $pim);
    }
}
