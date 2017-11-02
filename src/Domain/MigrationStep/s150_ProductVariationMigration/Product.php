<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

/**
 * Product data used for the variations migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Product
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var string */
    private $creationDate;

    /** @var string */
    private $variantGroupCode;

    /** @var int */
    private $familyId;

    public function __construct(int $id, string $identifier, int $familyId, string $creationDate, string $variantGroupCode)
    {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->creationDate = $creationDate;
        $this->variantGroupCode = $variantGroupCode;
        $this->familyId = $familyId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getCreationDate(): string
    {
        return $this->creationDate;
    }

    public function getVariantGroupCode(): string
    {
        return $this->variantGroupCode;
    }

    public function getFamilyId(): int
    {
        return $this->familyId;
    }
}
