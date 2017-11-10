<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Family;

/**
 * Represents a combination of variant groups by family and axes.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombination
{
    /** @var Family */
    private $family;

    /** @var array */
    private $axes;

    /** @var array */
    private $groups;

    /** @var array */
    private $attributes;

    public function __construct(Family $family, array $axes, array $groups, array $attributes)
    {
        $this->family = $family;
        $this->axes = $axes;
        $this->groups = $groups;
        $this->attributes = $attributes;
    }

    public function getFamily(): Family
    {
        return $this->family;
    }

    public function getAxes(): array
    {
        return $this->axes;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
