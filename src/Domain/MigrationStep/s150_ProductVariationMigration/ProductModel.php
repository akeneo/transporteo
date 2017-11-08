<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

/**
 * Data of a product model used for the migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModel
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var string */
    private $familyVariantCode;

    /** @var array */
    private $categories;

    /** @var array */
    private $values;

    public function __construct(?int $id, string $identifier, string $familyVariantCode, array $categories, array $values)
    {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->familyVariantCode = $familyVariantCode;
        $this->categories = $categories;
        $this->values = $values;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getFamilyVariantCode(): string
    {
        return $this->familyVariantCode;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
