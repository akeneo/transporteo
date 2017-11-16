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

    public function __construct(int $id, string $identifier)
    {
        $this->id = $id;
        $this->identifier = $identifier;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
