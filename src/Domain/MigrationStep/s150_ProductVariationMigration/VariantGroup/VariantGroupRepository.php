<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\Api\GetAttributeCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Aims to retrieve data related to the migration of the variant groups.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupRepository
{
    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    public function retrieveNumberOfVariantGroups(Pim $pim): int
    {
        return $this->retrieveNumberOfVariantGroupsByType($pim, 'VARIANT');
    }

    public function retrieveNumberOfRemovedInvalidVariantGroups(Pim $pim): int
    {
        return $this->retrieveNumberOfVariantGroupsByType($pim, 'MVARIANT');
    }

    public function retrieveVariantGroups(Pim $pim): \Traversable
    {
        $query =
<<<SQL
SELECT g.code,
(SELECT COUNT(*) FROM pim_catalog_group_attribute axe WHERE axe.group_id = g.id) as nb_axes,
(
    SELECT COUNT(DISTINCT f.id)
    FROM pim_catalog_group_product gp
    INNER JOIN pim_catalog_product p ON gp.product_id = p.id
    INNER JOIN pim_catalog_family f ON p.family_id = f.id
    WHERE gp.group_id = g.id
) as nb_families
FROM pim_catalog_group_type gt
INNER JOIN pim_catalog_group g ON g.type_id = gt.id
WHERE gt.code = "VARIANT"
SQL;

        $variantGroups = $this->console->execute(new MySqlQueryCommand($query), $pim)->getOutput();

        foreach ($variantGroups as $variantGroup) {
            yield new VariantGroup($variantGroup['code'], (int) $variantGroup['nb_axes'], (int) $variantGroup['nb_families']);
        }
    }

    public function retrieveVariantGroupCombinations(Pim $pim): array
    {
        $query =
<<<SQL
SELECT f.code as family_code, f.id as family_id,
(
	SELECT GROUP_CONCAT(DISTINCT a.code ORDER BY a.code ASC SEPARATOR ',')
	FROM pim_catalog_group_attribute axe
	INNER JOIN pim_catalog_attribute a ON axe.attribute_id = a.id
	WHERE axe.group_id = g.id
) as axes
, GROUP_CONCAT(DISTINCT g.code SEPARATOR ',') as groups
FROM pim_catalog_group g
    INNER JOIN pim_catalog_group_type gt ON gt.id = g.type_id
    INNER JOIN pim_catalog_group_product gp ON g.id =  gp.group_id
    INNER JOIN pim_catalog_product p ON gp.product_id = p.id
    INNER JOIN pim_catalog_family f ON p.family_id = f.id
WHERE gt.code = 'VARIANT'
GROUP BY family_code, axes
ORDER BY f.code;
SQL;

        return $this->console->execute(new MySqlQueryCommand($query), $pim)->getOutput();
    }

    public function retrieveVariantGroupsAxes(int $variantGroupId, Pim $pim)
    {
        return $this->console->execute(
            new MySqlQueryCommand(
                'SELECT code, attribute_type FROM pim_catalog_group_attribute
                INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id = attribute_id
                WHERE group_id = '.$variantGroupId
            ),
            $pim
        )->getOutput();
    }

    /**
     * Retrieves the attributes codes of a variant group.
     */
    public function retrieveGroupAttributes(string $groupCode, Pim $pim): array
    {
        $attributeValues = $this->retrieveGroupAttributeValues($groupCode, $pim);

        return array_keys($attributeValues);
    }

    /**
     * Retrieves the attributes values of a variant group.
     */
    public function retrieveGroupAttributeValues(string $groupCode, Pim $pim): array
    {
        $results = $this->console->execute(
            new MySqlQueryCommand(sprintf(
                'SELECT t.valuesData FROM pim_catalog_group g
                INNER JOIN pim_catalog_product_template t ON t.id = g.product_template_id
                WHERE g.code = "%s"',
                $groupCode
            )),
            $pim
        )->getOutput();

        if (!isset($results[0]['valuesData'])) {
            return [];
        }

        $attributeValues = json_decode($results[0]['valuesData'], true);

        if (!is_array($attributeValues)) {
            throw new \RuntimeException('Failed to decode the attribute values of the variant-group '.$groupCode);
        }

        return $attributeValues;
    }

    public function retrieveAttributeData(string $attributeCode, Pim $pim): array
    {
        $command = new GetAttributeCommand($attributeCode);

        return $this->console->execute($command, $pim)->getOutput();
    }

    public function retrieveVariantGroupCategories(string $variantGroupCode, Pim $pim): array
    {
        $categories = [];

        $results = $this->console->execute(
            new MySqlQueryCommand(sprintf(
                'SELECT DISTINCT c.code FROM pim_catalog_group g
                INNER JOIN pim_catalog_group_product gp ON gp.group_id = g.id
                INNER JOIN pim_catalog_product p ON p.id = gp.product_id
                INNER JOIN pim_catalog_category_product cp ON cp.product_id = p.id
                INNER JOIN pim_catalog_category c ON c.id = cp.category_id
                WHERE g.code = "%s"',
                $variantGroupCode
            )),
            $pim
        )->getOutput();

        foreach ($results as $result) {
            $categories[] = $result['code'];
        }

        return $categories;
    }

    private function retrieveNumberOfVariantGroupsByType(Pim $pim, string $type): int
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT COUNT(g.id) as nb_variant_groups
            FROM pim_catalog_group_type gt
            INNER JOIN pim_catalog_group g ON g.type_id = gt.id
            WHERE gt.code = "%s"',
            $type
        )), $pim)->getOutput();

        return isset($sqlResult[0]['nb_variant_groups']) ? (int) $sqlResult[0]['nb_variant_groups'] : 0;
    }
}
