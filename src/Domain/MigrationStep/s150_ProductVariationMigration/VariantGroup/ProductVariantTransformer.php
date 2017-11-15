<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Transforms products into product variants.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductVariantTransformer
{
    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    public function transformFromProductModel(ProductModel $productModel, FamilyVariant $familyVariant, DestinationPim $pim)
    {
        $query = sprintf(
            "UPDATE pim_catalog_product p"
            ." INNER JOIN pim_catalog_group_product gp ON gp.product_id = p.id"
            ." INNER JOIN pim_catalog_group g ON g.id = gp.group_id"
            ." SET p.product_model_id = %s, p.family_variant_id = %s, p.product_type = 'variant_product'",
            $productModel->getId(),
            $familyVariant->getId()
        );

        $attributesToRemove = array_keys($productModel->getAttributeValues());

        if (!empty($attributesToRemove)) {
            $query .= ", raw_values = JSON_REMOVE(raw_values";
            foreach ($attributesToRemove as $attribute) {
                $query .= sprintf(", '$.%s'", $attribute);
            }
            $query .= ")";
        }

        $query .= sprintf(" WHERE g.code = '%s'", $productModel->getIdentifier());

        $this->console->execute(new MySqlExecuteCommand($query), $pim);
    }
}
