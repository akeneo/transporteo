<?php

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

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

    /** @var int */
    private $variationFamilyId;

    /** @var array */
    private $axes;

    public function __construct(int $id, string $code, int $variationFamilyId, array $axes)
    {
        $this->id = $id;
        $this->code = $code;
        $this->variationFamilyId = $variationFamilyId;
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

    public function getVariationFamilyId(): int
    {
        return $this->variationFamilyId;
    }

    public function getAxes(): array
    {
        return $this->axes;
    }

    public function getAxesCodes(): array
    {
        $axesCodes = [];

        foreach ($this->axes as $axe) {
            $axesCodes[] = $axe['code'];
        }

        return $axesCodes;
    }
}
