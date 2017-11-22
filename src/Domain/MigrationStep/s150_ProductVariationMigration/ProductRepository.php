<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Product;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductRepository
{
    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    public function getCategoryCodes(int $productId, Pim $pim): array
    {
        $sqlResults = $this->console->execute(new MySqlQueryCommand(
            'SELECT code FROM pim_catalog_category
            INNER JOIN pim_catalog_category_product ON category_id = pim_catalog_category.id
            WHERE product_id = '.$productId
        ), $pim)->getOutput();

        $categories = [];
        foreach ($sqlResults as $sqlResult) {
            $categories[] = $sqlResult['code'];
        }

        return $categories;
    }

    public function findAllHavingVariantsForIvb(int $parentFamilyId, int $innerVariationFamilyId, Pim $pim): \Traversable
    {
        $sqlResults = $this->console->execute(new MySqlQueryCommand(sprintf(
                'SELECT id, identifier FROM pim_catalog_product AS product_model
            WHERE family_id = %d AND EXISTS(
                SELECT * FROM pim_catalog_product AS product_variant
                WHERE product_variant.family_id = %d
                AND JSON_EXTRACT(product_variant.raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = product_model.identifier
            );', $parentFamilyId, $innerVariationFamilyId)
        ), $pim)->getOutput();

        foreach ($sqlResults as $result) {
            yield new Product((int) $result['id'], $result['identifier']);
        }
    }

    public function findAllNotMigratedProductVariants(Pim $pim): \Iterator
    {
        $sqlResults = $this->console->execute(new MySqlQueryCommand(
            "SELECT id, identifier FROM pim_catalog_product	
             WHERE JSON_CONTAINS_PATH(raw_values, 'one', '$.variation_parent_product')"
        ), $pim)->getOutput();

        foreach ($sqlResults as $result) {
            yield new Product((int) $result['id'], $result['identifier']);
        }
    }

    public function delete(string $productIdentifier, Pim $pim): void
    {
        $this->console->execute(new DeleteProductCommand($productIdentifier), $pim);
    }
}
