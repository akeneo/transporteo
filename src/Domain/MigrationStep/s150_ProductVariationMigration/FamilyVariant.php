<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

/**
 * Data of a family variant used for the migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariant
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var array */
    private $attributes;

    /** @var array */
    private $productModelAttributes;

    public function __construct(int $id, string $code, array $attributes, array $productModelAttributes)
    {
        $this->id = $id;
        $this->code = $code;
        $this->attributes = $attributes;
        $this->productModelAttributes = $productModelAttributes;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getProductModelAttributes(): array
    {
        return $this->productModelAttributes;
    }
}
