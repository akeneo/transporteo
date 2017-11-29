<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeValidator;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeValidatorSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationTypeValidator::class);
    }

    public function it_returns_true_if_the_inner_variation_type_can_be_migrated()
    {
        $family = new Family(10, 'an_inner_variation_family', []);

        $innerVariationType = new InnerVariationType(
            1, 'ivt_with_five_axes', $family, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_3', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_4', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'axe_5', 'attribute_type' => 'pim_catalog_simpleselect'],
            ]
        );

        $this->canInnerVariationTypeBeMigrated($innerVariationType)->shouldReturn(true);
    }

    public function it_returns_false_if_the_inner_variation_type_has_more_than_five_axes()
    {
        $family = new Family(10, 'an_inner_variation_family', []);

        $innerVariationType = new InnerVariationType(
        2, 'ivt_with_six_axes', $family, [
            ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axe_2', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axe_3', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axe_4', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axe_5', 'attribute_type' => 'pim_catalog_simpleselect'],
            ['code' => 'axe_6', 'attribute_type' => 'pim_catalog_simpleselect'],
        ]
    );

        $this->canInnerVariationTypeBeMigrated($innerVariationType)->shouldReturn(false);
    }

    public function it_returns_false_if_the_inner_variation_type_has_an_invalid_axe()
    {
        $family = new Family(10, 'an_inner_variation_family', []);

        $innerVariationType = new InnerVariationType(
            2, 'invalid_ivt', $family, [
                ['code' => 'axe_1', 'attribute_type' => 'pim_catalog_simpleselect'],
                ['code' => 'invalid_axe', 'attribute_type' => 'pim_catalog_identifier'],
            ]
        );

        $this->canInnerVariationTypeBeMigrated($innerVariationType)->shouldReturn(false);
    }
}
