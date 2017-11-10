<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Repository for family data on the destination PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var FamilyVariantImporter */
    private $familyVariantImporter;

    /** @var array */
    private $familyCache = [];

    public function __construct(ChainedConsole $console, FamilyVariantImporter $familyVariantImporter)
    {
        $this->console = $console;
        $this->familyVariantImporter = $familyVariantImporter;
    }

    public function persistFamilyVariant(FamilyVariant $familyVariant, DestinationPim $pim): void
    {
        $familyVariantData = [
            'code' => $familyVariant->getCode(),
            'family' => $familyVariant->getFamilyCode(),
            'variant-axes_1' => implode(',', $familyVariant->getLevelOneAxes()),
            'variant-axes_2' => implode(',', $familyVariant->getLevelTwoAxes()),
            'variant-attributes_1' => implode(',', $familyVariant->getLevelOneAttributes()),
            'variant-attributes_2' => implode(',', $familyVariant->getLevelTwoAttributes()),
        ];

        foreach ($familyVariant->getLabels() as $locale => $label) {
            $familyVariantData['label-'.$locale] = $label;
        }

        $this->familyVariantImporter->import([$familyVariantData], $pim);
    }

    public function findByCode(string $familyCode, DestinationPim $pim): Family
    {
        if (isset($this->familyCache[$familyCode])) {
            return $this->familyCache[$familyCode];
        }

        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_family WHERE code = "%s"',
            $familyCode
        )), $pim)->getOutput();

        if (!isset($sqlResult[0]['id'])) {
            throw new \RuntimeException('Failed to find the family '.$familyCode);
        }

        $familyData = $this->console->execute(new GetFamilyCommand($familyCode), $pim)->getOutput();
        $family = new Family((int) $sqlResult[0]['id'], $familyCode, $familyData);
        $this->familyCache[$familyCode] = $family;

        return $family;
    }

    public function retrieveFamilyVariantId(string $familyVariantCode, DestinationPim $pim): ?int
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_family_variant WHERE code = "%s"',
            $familyVariantCode
        )), $pim)->getOutput();

        return isset($sqlResult[0]['id']) ? (int) $sqlResult[0]['id'] : null;
    }
}
