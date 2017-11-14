<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Pim\DestinationPim;

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
    private $levelOneAttributes;

    /** @var array */
    private $levelTwoAttributes;

    /** @var array */
    private $levelOneAxes;

    /** @var array */
    private $levelTwoAxes;

    /** @var array */
    private $labels;

    /** @var string */
    private $familyCode;

    public function __construct(
        ?int $id,
        string $code,
        string $familyCode,
        array $levelOneAxes,
        array $levelTwoAxes,
        array $levelOneAttributes,
        array $levelTwoAttributes,
        array $labels
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->levelOneAxes = $levelOneAxes;
        $this->levelTwoAxes = $levelTwoAxes;
        $this->levelOneAttributes = $levelOneAttributes;
        $this->levelTwoAttributes = $levelTwoAttributes;
        $this->labels = $labels;
        $this->familyCode = $familyCode;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLevelOneAttributes(): array
    {
        return $this->levelOneAttributes;
    }

    public function getLevelTwoAttributes(): array
    {
        return $this->levelTwoAttributes;
    }

    public function getLevelOneAxes(): array
    {
        return $this->levelOneAxes;
    }

    public function getLevelTwoAxes(): array
    {
        return $this->levelTwoAxes;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }
}
