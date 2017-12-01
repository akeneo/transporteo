<?php

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity;

/**
 * Data of an InnerVariationType used to the migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationType
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var Family */
    private $variationFamily;

    /** @var array */
    private $axes;

    public function __construct(int $id, string $code, Family $variationFamily, array $axes)
    {
        $this->id = $id;
        $this->code = $code;
        $this->variationFamily = $variationFamily;
        $this->axes = $axes;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVariationFamily(): Family
    {
        return $this->variationFamily;
    }

    public function getVariationFamilyId(): int
    {
        return $this->variationFamily->getId();
    }

    public function getAxes(): array
    {
        return $this->axes;
    }

    public function getAxesCodes(): array
    {
        $axesCodes = [];

        foreach ($this->axes as $axis) {
            $axesCodes[] = $axis['code'];
        }

        return $axesCodes;
    }
}
