<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Aims to retrieve data related to the migration of the inner variation types.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationRetriever
{
    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    /**
     * Retrieves the MySQL tables related to the InnerVariationBundle.
     */
    public function retrieveInnerVariationTables(Pim $pim): array
    {
        return $this->console->execute(
            new MySqlQueryCommand('SHOW TABLES LIKE "pim_inner_variation_inner_variation%"'),
            $pim
        )->getOutput();
    }

    /**
     * Retrieves all the InnerVariationType occurrences of a PIM.
     */
    public function retrieveInnerVariationTypes(Pim $pim): array
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
                $this->retrieveAxes($id, $pim)
            );
        }

        return $innerVariationTypes;
    }

    /**
     * Retrieves the axes data of a given InnerVariationType id.
     */
    public function retrieveAxes(int $innerVariationTypeId, Pim $pim): array
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
     * Retrieves the family variant data of an InnerVariationType.
     */
    public function retrieveInnerVariationFamily(InnerVariationType $innerVariationType, Pim $pim): Family
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
    public function retrieveParentFamilies(InnerVariationType $innerVariationType, Pim $pim): array
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

        $parentFamilies = [];
        foreach ($parentFamiliesData as $parentFamilyData) {
            $parentFamilies[] = $this->buildFamily((int) $parentFamilyData['id'], $parentFamilyData['code'], $pim);
        }

        return $parentFamilies;
    }

    /**
     * Retrieves the label of an InnerVariationType for a given locale.
     */
    public function retrieveInnerVariationLabel(InnerVariationType $innerVariationType, string $locale, Pim $pim): string
    {
        $innerVariationTypeLabel = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT label FROM pim_inner_variation_inner_variation_type_translation
            WHERE foreign_key = %d AND locale = "%s"',
            $innerVariationType->getId(),
            $locale
        )), $pim)->getOutput();

        return empty($innerVariationTypeLabel) ? '' : $innerVariationTypeLabel[0]['label'];
    }

    /**
     * Retrieves the products having products variants given a family and a variation family.
     */
    public function retrievesFamilyProductsHavingVariants(int $parentFamilyId, int $innerVariationFamilyId, Pim $pim): array
    {
        return $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id, identifier, raw_values, created FROM pim_catalog_product AS product_model
            WHERE family_id = %d AND EXISTS(
                SELECT * FROM pim_catalog_product AS product_variant
                WHERE product_variant.family_id = %d
                AND JSON_EXTRACT(product_variant.raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = product_model.identifier
            );', $parentFamilyId, $innerVariationFamilyId)
        ), $pim)->getOutput();
    }

    public function retrieveProductCategories(int $productId, Pim $pim): array
    {
        $sqlResults = $this->console->execute(new MySqlQueryCommand(
            'SELECT code FROM pim_catalog_category
            INNER JOIN pim_catalog_category_product ON category_id = pim_catalog_category.id
            WHERE product_id = '.$productId
        ), $pim)->getOutput();

        $categories = [];
        foreach ($sqlResults as $sqlResult) {
            $categories[] = $sqlResult['code'];
        }

        return $categories;
    }

    /**
     * Retrieves the product model internal id from its identifier.
     */
    public function retrieveProductModelId(string $identifier, Pim $pim): ?int
    {
        $productData = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_product_model WHERE code = "%s"', $identifier
        )), $pim)->getOutput();

        return empty($productData) ? null : (int) $productData[0]['id'];
    }

    /**
     * Retrieves the data of a family variant from its parent families.
     */
    public function retrieveFamilyVariant(Family $parentFamily, Family $innerVariationFamily, Pim $pim)
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id, family_id, code FROM pim_catalog_family_variant WHERE code = "%s"',
            $parentFamily->getCode().'_'.$innerVariationFamily->getCode()
        )), $pim)->getOutput();

        return empty($sqlResult) ? [] : $sqlResult[0];
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
