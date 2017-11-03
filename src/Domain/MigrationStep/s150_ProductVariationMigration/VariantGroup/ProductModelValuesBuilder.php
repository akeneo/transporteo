<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Builds product model values.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelValuesBuilder
{
    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    public function __construct(VariantGroupRepository $variantGroupRepository)
    {
        $this->variantGroupRepository = $variantGroupRepository;
    }

    public function buildFromVariantGroup(string $variantGroupCode, DestinationPim $pim): array
    {
        $producModelValues = [];
        $variantGroupValues = $this->variantGroupRepository->retrieveGroupAttributeValues($variantGroupCode, $pim);

        foreach ($variantGroupValues as $attribute => $values) {
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
                        $producModelValues[$attributeValueKey] = $value['data']['amount'];
                        $producModelValues[$attributeValueKey.'-unit'] = $value['data']['unit'];
                    } elseif (isset($value['data'][0]['currency'])) {
                        foreach ($value['data'] as $price) {
                            $producModelValues[$attributeValueKey.'-'.$price['currency']] = $price['amount'];
                        }
                    }
                } else {
                    $producModelValues[$attributeValueKey] = $value['data'];
                }
            }
        }

        return $producModelValues;
    }
}
