<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\CreateFamilyVariantCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationFamilyMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariationRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupCombination;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroupRetriever;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationFamilyMigratorSpec extends ObjectBehavior
{
    public function let(
        ChainedConsole $console,
        MixedVariationRetriever $mixedVariationRetriever,
        VariantGroupRetriever $variantGroupRetriever,
        InnerVariationRetriever $innerVariationRetriever,
        InnerVariationFamilyMigrator $innerVariationFamilyMigrator
    ) {
        $this->beConstructedWith($console, $mixedVariationRetriever, $variantGroupRetriever, $innerVariationRetriever, $innerVariationFamilyMigrator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MixedVariationFamilyMigrator::class);
    }

    public function it_successfully_migrates_family_variant(
        DestinationPim $pim,
        $console,
        $mixedVariationRetriever,
        $variantGroupRetriever,
        $innerVariationRetriever,
        $innerVariationFamilyMigrator
    ) {

        $variantGroupCombination = new VariantGroupCombination('family_parent', 'family_variant', ['vg_axe_1', 'vg_axe_2'], ['group_1', 'group_2']);
        $innerVariationType = new InnerVariationType(11, 'ivt_1', 32, [['code' => 'ivt_axe']]);

        $family = new Family(41, 'family_parent', [
            'attributes' => ['family_parent_att_1', 'vg_axe_1', 'vg_axe_2', 'variation_family_att_1', 'group_att_1', 'group_att_2'],
            'labels' => [
                'en_US' => 'Parent family',
                'fr_FR' => 'Famille parent'
            ]
        ]);

        $innerVariationFamily = new Family(42, 'variation_family', [
            'attributes' => ['variation_family_att_1', 'ivt_axe', 'variation_parent_product'],
            'labels' => [
                'en_US' => 'Variation family',
                'fr_FR' => 'Famille de variation'
            ]
        ]);

        $mixedVariationRetriever->retrieveFamilyByCode($variantGroupCombination->getFamilyCode(), $pim)->willReturn($family);
        $mixedVariationRetriever->retrieveFamilyById($innerVariationType->getVariationFamilyId(), $pim)->willReturn($innerVariationFamily);

        $innerVariationFamilyMigrator->migrateFamilyAttributes($family, $innerVariationFamily, $pim)->shouldBeCalled();

        $variantGroupRetriever->retrieveGroupAttributes('group_1', $pim)->willReturn(['group_att_1', 'group_att_2']);

        $variantGroupRetriever->retrieveAttributeData('vg_axe_1', $pim)->willReturn([
            'labels' => [
                'en_US' => 'VG axe 1',
                'fr_FR' => null,
            ]
        ]);

        $variantGroupRetriever->retrieveAttributeData('vg_axe_2', $pim)->willReturn([
            'labels' => [
                'en_US' => 'VG axe 2',
                'fr_FR' => 'Axe 2 VG',
            ]
        ]);

        $innerVariationRetriever->retrieveInnerVariationLabel($innerVariationType, 'en_US', $pim)->willReturn('');
        $innerVariationRetriever->retrieveInnerVariationLabel($innerVariationType, 'fr_FR', $pim)->willReturn('IVT FR');

        $familyVariantData = [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'attributes' => ['family_parent_att_1'],
                    'axes' => $variantGroupCombination->getAxes()
                ],
                [
                    'level' => 2,
                    'attributes' => ['variation_family_att_1'],
                    'axes' => $innerVariationType->getAxesCodes()
                ]
            ],
            'labels' => [
                'en_US' => 'Parent family VG axe 1 VG axe 2',
                'fr_FR' => 'Famille parent Axe 2 VG IVT FR'
            ]
        ];

        $createFamilyVariantCommand = new CreateFamilyVariantCommand('family_parent', 'family_variant', $familyVariantData);
        $console->execute($createFamilyVariantCommand, $pim)->shouldBeCalled();

        $variantGroupRetriever->retrieveFamilyVariantId('family_variant', $pim)->willReturn(76);

        $this->migrateFamilyVariant($variantGroupCombination, $innerVariationType, $pim);
    }
}
