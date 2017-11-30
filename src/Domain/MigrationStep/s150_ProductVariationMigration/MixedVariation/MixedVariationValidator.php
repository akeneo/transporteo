<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeValidator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupValidator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Validates mixed variation VG + IVB
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationValidator
{
    /** @var VariantGroupValidator */
    private $variantGroupValidator;

    /** @var InnerVariationTypeValidator */
    private $innerVariationTypeValidator;

    public function __construct(VariantGroupValidator $variantGroupValidator, InnerVariationTypeValidator $innerVariationTypeValidator)
    {
        $this->variantGroupValidator = $variantGroupValidator;
        $this->innerVariationTypeValidator = $innerVariationTypeValidator;
    }

    public function isValid(MixedVariation $mixedVariation, DestinationPim $pim)
    {
        if (!$this->innerVariationTypeValidator->canInnerVariationTypeBeMigrated($mixedVariation->getInnerVariationType())) {
            return false;
        }

        foreach ($mixedVariation->getVariantGroups() as $variantGroup) {
            if (!$this->variantGroupValidator->isVariantGroupValid($variantGroup)) {
                return false;
            }
        }

        if (!$this->variantGroupValidator->isVariantGroupCombinationValid($mixedVariation->getVariantGroupCombination(), $pim)) {
            return false;
        }

        return true;
    }
}
