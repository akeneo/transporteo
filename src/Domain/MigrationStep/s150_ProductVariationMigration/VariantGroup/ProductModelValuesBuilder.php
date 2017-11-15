<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\ProductModel;

/**
 * Builds product model values to import them.
 *
 * TODO: Remove this class when the import will be replaced by the API.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelValuesBuilder
{
    public function build(ProductModel $productModel): array
    {
        $productModelValues = [];

        foreach ($productModel->getAttributeValues() as $attribute => $values) {
            foreach ($values as $value) {
                $attributeValueKey = $attribute;

                if (null !== $value['locale']) {
                    $attributeValueKey .= '-'.$value['locale'];
                }
                if (null !== $value['scope']) {
                    $attributeValueKey .= '-'.$value['scope'];
                }
                if (is_array($value['data'])) {
                    if (isset($value['data']['unit'])) {
                        $productModelValues[$attributeValueKey] = $value['data']['amount'];
                        $productModelValues[$attributeValueKey.'-unit'] = $value['data']['unit'];
                    } elseif (isset($value['data'][0]['currency'])) {
                        foreach ($value['data'] as $price) {
                            $productModelValues[$attributeValueKey.'-'.$price['currency']] = $price['amount'];
                        }
                    }
                } else {
                    $productModelValues[$attributeValueKey] = $value['data'];
                }
            }
        }

        return $productModelValues;
    }
}
