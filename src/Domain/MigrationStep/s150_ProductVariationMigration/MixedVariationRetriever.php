<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Aims to retrieve data related to the migration of mixed variant groups and inner variation types.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationRetriever
{
    /** @var ChainedConsole */
    private $console;

    /** @var InnerVariationRetriever */
    private $innerVariationRetriever;

    public function __construct(ChainedConsole $console, InnerVariationRetriever $innerVariationRetriever)
    {
        $this->console = $console;
        $this->innerVariationRetriever = $innerVariationRetriever;
    }

    public function retrieveInnerVariationTypeByFamilyCode(string $familyCode, Pim $pim): ?InnerVariationType
    {
        $innerVariationTypeData = $this->console->execute(
            new MySqlQueryCommand(sprintf(
                'SELECT ivt.id, ivt.code, ivt.variation_family_id 
                FROM pim_inner_variation_inner_variation_type ivt
                INNER JOIN pim_inner_variation_inner_variation_type_family ivtf ON ivtf.inner_variation_type_id = ivt.id
                INNER JOIN pim_catalog_family f ON f.id = ivtf.family_id
                WHERE f.code = "%s"'
                , $familyCode)),
            $pim
        )->getOutput();

        if (empty($innerVariationTypeData)) {
            return null;
        }

        $innerVariationTypeData = $innerVariationTypeData[0];
        $innerVariationTypeId = (int) $innerVariationTypeData['id'];

        return new InnerVariationType(
            $innerVariationTypeId,
            $innerVariationTypeData['code'],
            (int) $innerVariationTypeData['variation_family_id'],
            $this->innerVariationRetriever->retrieveAxes($innerVariationTypeId, $pim)
        );
    }

    // TODO: move in a service not specific to mixed variation.
    public function retrieveFamilyByCode(string $familyCode, Pim $pim): Family
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_family WHERE code = "%s"',
            $familyCode
        )), $pim)->getOutput();

        if (!isset($sqlResult[0]['id'])) {
            throw new \RuntimeException('Unable to retrieve the family '.$familyCode);
        }

        $familyId = (int) $sqlResult[0]['id'];
        $familyData = $this->console->execute(new GetFamilyCommand($familyCode), $pim)->getOutput();

        return new Family($familyId, $familyCode, $familyData);
    }

    // TODO: move in a service not specific to mixed variation.
    public function retrieveFamilyById(int $familyId, Pim $pim): Family
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT code FROM pim_catalog_family WHERE id = %d',
            $familyId
        )), $pim)->getOutput();

        if (!isset($sqlResult[0]['code'])) {
            throw new \RuntimeException('Unable to retrieve the family '.$familyId);
        }

        $familyCode = $sqlResult[0]['code'];
        $familyData = $this->console->execute(new GetFamilyCommand($familyCode), $pim)->getOutput();

        return new Family($familyId, $familyCode, $familyData);
    }

    public function retrieveProductsHavingVariantsByGroups(array $variantGroups, int $innerVariationFamilyId, Pim $pim): array
    {
        $results = $this->console->execute(new MySqlQueryCommand(sprintf(
                'SELECT DISTINCT p.id, p.identifier, p.created, p.family_id ,g.code AS variant_group_code
            FROM pim_catalog_group g
            INNER JOIN pim_catalog_group_product gp ON gp.group_id = g.id
            INNER JOIN pim_catalog_product p ON p.id = gp.product_id
            WHERE g.code IN("%s")
            AND EXISTS(
              SELECT * FROM pim_catalog_product AS product_variant
              WHERE product_variant.family_id = %d
              AND JSON_EXTRACT(product_variant.raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = p.identifier
            )', implode('","', $variantGroups), $innerVariationFamilyId)
        ), $pim)->getOutput();

        $products = [];
        foreach ($results as $productData) {
            $products[] = new Product(
                (int) $productData['id'],
                $productData['identifier'],
                (int) $productData['family_id'],
                $productData['created'],
                $productData['variant_group_code']
            );
        }

        return $products;
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
}
