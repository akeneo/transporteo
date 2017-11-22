<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
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

    public function transform(ProductModel $productModel, FamilyVariant $familyVariant, Family $parentFamily, Family $innerVariationFamily, Pim $pim): void
    {
        $command = new MySqlExecuteCommand(sprintf(
            'UPDATE pim_catalog_product SET'
            .' family_id = %s, product_model_id = %s, family_variant_id = %s,'
            .' product_type = "variant_product", raw_values = JSON_REMOVE(raw_values, \'$.variation_parent_product\')'
            .' WHERE family_id = %s'
            .' AND JSON_EXTRACT(raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = "%s"',
            $parentFamily->getId(),
            $productModel->getId(),
            $familyVariant->getId(),
            $innerVariationFamily->getId(),
            $productModel->getIdentifier()
        ));

        $this->console->execute($command, $pim);
    }
}
