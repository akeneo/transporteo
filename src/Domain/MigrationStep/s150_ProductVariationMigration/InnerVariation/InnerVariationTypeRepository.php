<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Aims to retrieve data related to the migration of the inner variation types.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeRepository
{
    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    /**
     * Return array of all InnerVariationType occurrences of a PIM.
     */
    public function findAll(Pim $pim): array
    {
        $innerVariationTypesData = $innerVariationTables = $this->console->execute(
            new MySqlQueryCommand('SELECT id, code, variation_family_id FROM pim_inner_variation_inner_variation_type'),
            $pim
        )->getOutput();

        $innerVariationTypes = [];
        foreach ($innerVariationTypesData as $innerVariationTypeData) {
            $id = (int) $innerVariationTypeData['id'];

            $innerVariationTypes[] = new InnerVariationType(
                $id,
                $innerVariationTypeData['code'],
                (int) $innerVariationTypeData['variation_family_id'],
                $this->getAxes($id, $pim)
            );
        }

        return $innerVariationTypes;
    }

    /**
     * Retrieves the family variant data of an InnerVariationType.
     */
    public function getFamily(InnerVariationType $innerVariationType, Pim $pim): Family
    {
        $innerVariationFamily = $this->console->execute(
            new MySqlQueryCommand('SELECT id, code FROM pim_catalog_family WHERE id = '.$innerVariationType->getVariationFamilyId()),
            $pim
        )->getOutput();

        if (empty($innerVariationFamily)) {
            throw new \RuntimeException('Unable te retrieve the family having id = '.$innerVariationType->getVariationFamilyId());
        }

        return $this->buildFamily((int) $innerVariationFamily[0]['id'], $innerVariationFamily[0]['code'], $pim);
    }

    /**
     * Retrieves the parent families related to an InnerVariationType.
     */
    public function getParentFamilies(InnerVariationType $innerVariationType, Pim $pim): \Traversable
    {
        $parentFamiliesData = $this->console->execute(
            new MySqlQueryCommand(
                'SELECT pim_catalog_family.code, pim_catalog_family.id
                FROM pim_inner_variation_inner_variation_type_family
                INNER JOIN pim_catalog_family ON pim_catalog_family.id = family_id
                WHERE inner_variation_type_id = '.$innerVariationType->getId()
            ),
            $pim
        )->getOutput();

        foreach ($parentFamiliesData as $parentFamilyData) {
            yield $this->buildFamily((int) $parentFamilyData['id'], $parentFamilyData['code'], $pim);
        }
    }

    /**
     * Retrieves the label of an InnerVariationType for a given locale.
     */
    public function getLabel(InnerVariationType $innerVariationType, string $locale, Pim $pim): string
    {
        $innerVariationTypeLabel = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT label FROM pim_inner_variation_inner_variation_type_translation
            WHERE foreign_key = %d AND locale = "%s"',
            $innerVariationType->getId(),
            $locale
        )), $pim)->getOutput();

        return $innerVariationTypeLabel[0]['label'] ?? '';
    }

    /**
     * Retrieves the axes data of a given InnerVariationType id.
     */
    private function getAxes(int $innerVariationTypeId, Pim $pim): array
    {
        return $this->console->execute(
            new MySqlQueryCommand(
                'SELECT code, attribute_type FROM pim_inner_variation_inner_variation_type_axis
                INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id = attribute_id
                WHERE inner_variation_type_id = '.$innerVariationTypeId
            ),
            $pim
        )->getOutput();
    }

    /**
     * Retrieves all the data of a family.
     */
    private function buildFamily(int $familyId, string $familyCode, Pim $pim): Family
    {
        $apiCommand = new GetFamilyCommand($familyCode);
        $familyStandardData = $this->console->execute($apiCommand, $pim)->getOutput();

        return new Family($familyId, $familyCode, $familyStandardData);
    }
}
