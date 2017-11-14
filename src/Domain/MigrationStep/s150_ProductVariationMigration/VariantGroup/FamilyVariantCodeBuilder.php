<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

/**
 * Builds the code of a family variant to migrate a variant group combination.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantCodeBuilder
{
    const MAX_LENGTH = 100;

    /**
     * Builds a family variant code by concatenating the codes of the parent family and the axes of a variant group combination.
     */
    public function buildFromVariantGroupCombination(VariantGroupCombination $variantGroupCombination): string
    {
        $familyCode = $variantGroupCombination->getFamily()->getCode();
        $axesCodes = implode('_', $variantGroupCombination->getAxes());
        
        $familyVariantCode = sprintf('%s_%s', $familyCode, $axesCodes);

        if (strlen($familyVariantCode) > self::MAX_LENGTH) {
            /**
             * Using an hash to limit the length of the family variant code.
             * We use md5 because it's a good compromise between length and reliability.
             * We keep the parent family code as prefix for readability purposes.
             * The function md5 returns a 32 characters string so we have to limit the length of the family parent code.
             */
            $familyVariantCode = sprintf('%s_%s', substr($familyCode, 0,67), md5($familyVariantCode));
        }

        return $familyVariantCode;
    }
}
