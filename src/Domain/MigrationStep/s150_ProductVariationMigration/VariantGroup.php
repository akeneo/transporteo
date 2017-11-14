<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

/**
 * Data of a VariantGroup used for the migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroup
{
    /** @var string */
    private $code;

    /** @var int */
    private $numberOfAxes;

    /** @var int */
    private $numberOfFamilies;

    public function __construct(string $code, int $numberOfAxes, int $numberOfFamilies)
    {
        $this->code = $code;
        $this->numberOfAxes = $numberOfAxes;
        $this->numberOfFamilies = $numberOfFamilies;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getNumberOfAxes(): int
    {
        return $this->numberOfAxes;
    }

    public function getNumberOfFamilies(): int
    {
        return $this->numberOfFamilies;
    }
}
