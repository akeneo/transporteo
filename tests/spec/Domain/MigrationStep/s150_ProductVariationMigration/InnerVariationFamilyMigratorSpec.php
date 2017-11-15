<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\UpdateFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationRetriever;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationFamilyMigratorSpec extends ObjectBehavior
{
    public function let(
        InnerVariationRetriever $innerVariationRetriever,
        FamilyVariantImporter $familyVariantImporter,
        ChainedConsole $console,
        LoggerInterface $logger
    )
    {
        $this->beConstructedWith($innerVariationRetriever, $familyVariantImporter, $console, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationFamilyMigrator::class);
    }

    public function it_successfully_migrates_families(
        $innerVariationRetriever,
        $familyVariantImporter,
        $console,
        InnerVariationType $innerVariationType,
        Pim $pim
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

        $innerVariationRetriever->retrieveInnerVariationFamily($innerVariationType, $pim)->willReturn($innerVariationFamily);

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

        $innerVariationRetriever->retrieveParentFamilies($innerVariationType, $pim)->willReturn([$firstParentFamily, $secondParentFamily]);

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
            ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_metric']
        ]);

        $innerVariationRetriever->retrieveInnerVariationLabel($innerVariationType, 'en_US', $pim)->willReturn('IVT US');
        $innerVariationRetriever->retrieveInnerVariationLabel($innerVariationType, 'fr_FR', $pim)->willReturn('IVT FR');

        $firstFamilyVariant = [
            'code' => 'first_parent_family_inner_variation_family',
            'family' => 'first_parent_family',
            'variant-axes_1' => 'axe_1,axe_2',
            'variant-axes_2' => '',
            'variant-attributes_1' => 'attribute_1, attribute_2',
            'variant-attributes_2' => '',
            'label-en_US' => 'First parent label US IVT US',
            'label-fr_FR' => 'First parent label FR IVT FR',
        ];

        $secondFamilyVariant = [
            'code' => 'second_parent_family_inner_variation_family',
            'family' => 'second_parent_family',
            'variant-axes_1' => 'axe_1,axe_2',
            'variant-axes_2' => '',
            'variant-attributes_1' => 'attribute_1, attribute_2',
            'variant-attributes_2' => '',
            'label-en_US' => 'Second parent label US IVT US',
            'label-fr_FR' => 'Second parent label FR IVT FR',
        ];

        $familyVariantImporter->import([$firstFamilyVariant, $secondFamilyVariant], $pim);

        $this->migrate($innerVariationType, $pim);
    }
}
