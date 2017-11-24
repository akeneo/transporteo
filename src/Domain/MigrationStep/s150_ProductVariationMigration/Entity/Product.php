<?php

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Product
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var int|null */
    private $familyId;

    /** @var null|string */
    private $createdAt;

    /** @var null|string */
    private $variantGroupCode;

    public function __construct(
        int $id,
        string $identifier,
        ?int $familyId,
        ?string $createdAt,
        ?string $variantGroupCode
    ) {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->familyId = $familyId;
        $this->createdAt = $createdAt;
        $this->variantGroupCode = $variantGroupCode;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getFamilyId(): int
    {
        return $this->familyId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getVariantGroupCode(): string
    {
        return $this->variantGroupCode;
    }
}
