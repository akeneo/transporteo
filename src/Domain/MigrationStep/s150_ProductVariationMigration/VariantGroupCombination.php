<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

/**
 * Represents a combination of variant groups by family and axes.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombination
{
    /** @var string */
    private $familyCode;

    /** @var string */
    private $familyVariantCode;

    /** @var array */
    private $axes;

    /** @var array */
    private $groups;

    public function __construct(string $familyCode, string $familyVariantCode, array $axes, array $groups)
    {
        $this->familyCode = $familyCode;
        $this->familyVariantCode = $familyVariantCode;
        $this->axes = $axes;
        $this->groups = $groups;
    }

    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }

    public function getFamilyVariantCode(): string
    {
        return $this->familyVariantCode;
    }

    public function getAxes(): array
    {
        return $this->axes;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}
