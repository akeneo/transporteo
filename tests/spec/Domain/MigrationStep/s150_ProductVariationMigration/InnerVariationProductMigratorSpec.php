<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationProductMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationRetriever;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\ProductModelImporter;
use Akeneo\PimMigration\Domain\Pim\Pim;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationProductMigratorSpec extends ObjectBehavior
{
    public function let(
        ChainedConsole $console,
        InnerVariationRetriever $innerVariationRetriever,
        ProductModelImporter $productModelImporter,
        LoggerInterface $logger
    )
    {
        $this->beConstructedWith($console, $innerVariationRetriever, $productModelImporter, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InnerVariationProductMigrator::class);
    }

    public function it_successfully_migrates_products(
        $console,
        $innerVariationRetriever,
        $productModelImporter,
        InnerVariationType $innerVariationType,
        Pim $pim
    )
    {
        $innerVariationFamily = new Family(1, 'inner_variation_family', []);
        $parentFamily = new Family(10, 'first_parent_family', []);

        $innerVariationRetriever->retrieveInnerVariationFamily($innerVariationType, $pim)->willReturn($innerVariationFamily);
        $innerVariationRetriever->retrieveParentFamilies($innerVariationType, $pim)->willReturn([$parentFamily]);
        $innerVariationRetriever->retrieveFamilyVariant($parentFamily, $innerVariationFamily, $pim)->willReturn([
            'id' => '20',
            'code' => 'first_family_variant',
            'family_id' => '10'
        ]);

        $innerVariationRetriever->retrievesFamilyProductsHavingVariants(10, 1, $pim)->willReturn([
            [
                'id' => '110',
                'identifier' => 'product_model_1',
                'created' => '2017-10-05 15:03:45'
            ],
            [
                'id' => '111',
                'identifier' => 'product_model_2',
                'created' => '2017-10-04 10:34:08'
            ]
        ]);

        $innerVariationRetriever->retrieveProductCategories(110, $pim)->willReturn(['cat_1', 'cat_2']);
        $innerVariationRetriever->retrieveProductCategories(111, $pim)->willReturn(['cat_1']);

        $productModelImporter->import([
            [
                'code' => 'product_model_1',
                'family_variant' => 'first_family_variant',
                'categories' => 'cat_1,cat_2',
                'parent' => '',
            ],
            [
                'code' => 'product_model_2',
                'family_variant' => 'first_family_variant',
                'categories' => 'cat_1',
                'parent' => '',
            ]
        ], $pim)->shouldBeCalled();

        $innerVariationRetriever->retrieveProductModelId('product_model_1', $pim)->willReturn(41);
        $innerVariationRetriever->retrieveProductModelId('product_model_2', $pim)->willReturn(42);

        $updateProductModelQuery = 'UPDATE pim_catalog_product_model AS product_model'
            .' INNER JOIN pim_catalog_product AS product ON product.identifier = product_model.code'
            .' SET product_model.raw_values = product.raw_values, product_model.created = product.created'
            .' WHERE product_model.id = ';

        $console->execute(new MySqlExecuteCommand($updateProductModelQuery.'41'), $pim)->shouldBeCalled();
        $console->execute(new MySqlExecuteCommand($updateProductModelQuery.'42'), $pim)->shouldBeCalled();

        $console->execute(new MySqlExecuteCommand(
            'UPDATE pim_catalog_product SET'
            .' family_id = 10, product_model_id = 41, family_variant_id = 20, created="2017-10-05 15:03:45",'
            .' product_type = "variant_product", raw_values = JSON_REMOVE(raw_values, \'$.variation_parent_product\')'
            .' WHERE family_id = 1'
            .' AND JSON_EXTRACT(raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = "product_model_1"'
        ), $pim)->shouldBeCalled();

        $console->execute(new MySqlExecuteCommand(
            'UPDATE pim_catalog_product SET'
            .' family_id = 10, product_model_id = 42, family_variant_id = 20, created="2017-10-04 10:34:08",'
            .' product_type = "variant_product", raw_values = JSON_REMOVE(raw_values, \'$.variation_parent_product\')'
            .' WHERE family_id = 1'
            .' AND JSON_EXTRACT(raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = "product_model_2"'
        ), $pim)->shouldBeCalled();

        $console->execute(new DeleteProductCommand('product_model_1'), $pim)->shouldBeCalled();
        $console->execute(new DeleteProductCommand('product_model_2'), $pim)->shouldBeCalled();

        $this->migrate($innerVariationType, $pim);
    }
}
