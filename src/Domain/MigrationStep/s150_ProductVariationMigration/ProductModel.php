<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

/**
 * Product model data used for the variations migration.
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

    /** @var int */
    private $familyId;

    public function __construct($id, $identifier, $familyId)
    {
        $this->id = $id;
        $this->identifier = $identifier;
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

    public function getFamilyId(): int
    {
        return $this->familyId;
    }
}
