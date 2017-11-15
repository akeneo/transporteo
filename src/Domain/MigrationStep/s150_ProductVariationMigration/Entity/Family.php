<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity;

/**
 * Data of a Family used to the migration.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Family
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var array */
    private $standardData;

    public function __construct(int $id, string $code, array $standardData)
    {
        $this->id = $id;
        $this->code = $code;
        $this->standardData = $standardData;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getStandardData(): array
    {
        return $this->standardData;
    }

    public function getAttributes(): array
    {
        return $this->standardData['attributes'] ?? [];
    }

    public function getLabels(): array
    {
        return $this->standardData['labels'] ?? [];
    }
}
