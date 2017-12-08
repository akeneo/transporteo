<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\Api\GetAttributeCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\VariantGroup;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Aims to read an write data related to the migration of the variant groups on the destination PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var int */
    private $invalidVariantGroupTypeId;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    public function retrieveNumberOfVariantGroups(DestinationPim $pim): int
    {
        return $this->retrieveNumberOfVariantGroupsByType($pim, 'VARIANT');
    }

    public function retrieveNumberOfRemovedInvalidVariantGroups(DestinationPim $pim): int
    {
        return $this->retrieveNumberOfVariantGroupsByType($pim, 'INVALID_VARIANT');
    }

    public function retrieveVariantGroups(DestinationPim $pim, array $codes = []): \Traversable
    {
        $query =
<<<SQL
SELECT g.code, COUNT(axis.group_id) as nb_axes,
(
    SELECT COUNT(DISTINCT f.id)
    FROM pim_catalog_group_product gp
    INNER JOIN pim_catalog_product p ON gp.product_id = p.id
    INNER JOIN pim_catalog_family f ON p.family_id = f.id
    WHERE gp.group_id = g.id
) as nb_families
FROM pim_catalog_group_type gt
INNER JOIN pim_catalog_group g ON g.type_id = gt.id
LEFT JOIN pim_catalog_group_attribute axis ON axis.group_id = g.id
WHERE gt.code = "VARIANT"
SQL;
        if (!empty($codes)) {
            $query .= sprintf(' AND g.code IN ("%s")', implode('","', $codes));
        }

        $query .= ' GROUP BY g.code';

        $variantGroups = $this->console->execute(new MySqlQueryCommand($query), $pim)->getOutput();

        foreach ($variantGroups as $variantGroup) {
            yield new VariantGroup($variantGroup['code'], (int) $variantGroup['nb_axes'], (int) $variantGroup['nb_families']);
        }
    }

    public function retrieveVariantGroupsAxes(int $variantGroupId, DestinationPim $pim)
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
    public function retrieveGroupAttributes(string $groupCode, DestinationPim $pim): array
    {
        $attributeValues = $this->retrieveGroupAttributeValues($groupCode, $pim);

        return array_keys($attributeValues);
    }

    /**
     * Retrieves the attributes values of a variant group.
     */
    public function retrieveGroupAttributeValues(string $groupCode, DestinationPim $pim): array
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

    public function retrieveAttributeData(string $attributeCode, DestinationPim $pim): array
    {
        $command = new GetAttributeCommand($attributeCode);

        return $this->console->execute($command, $pim)->getOutput();
    }

    public function retrieveVariantGroupCategories(string $variantGroupCode, DestinationPim $pim): array
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

    private function retrieveNumberOfVariantGroupsByType(DestinationPim $pim, string $type): int
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

    /**
     * Removes softly a variant-group from the migration by changing its type to a specific one.
     */
    public function removeSoftlyVariantGroup(string $variantGroupCode, DestinationPim $pim): void
    {
        $this->ensureInvalidVariantGroupTypeExists($pim);

        $query = sprintf(
            'UPDATE pim_catalog_group SET type_id = %d WHERE code = "%s"',
            $this->invalidVariantGroupTypeId,
            $variantGroupCode
        );

        $this->console->execute(new MySqlExecuteCommand($query), $pim);
    }

    private function ensureInvalidVariantGroupTypeExists(DestinationPim $pim): void
    {
        if (null !== $this->invalidVariantGroupTypeId) {
            return;
        }

        $migrationGroupTypeId = $this->retrieveInvalidVariantGroupTypeId($pim);

        if (null !== $migrationGroupTypeId) {
            $this->invalidVariantGroupTypeId = $migrationGroupTypeId;

            return;
        }

        $this->console->execute(new MySqlExecuteCommand(sprintf('INSERT INTO pim_catalog_group_type SET code = "INVALID_VARIANT"')), $pim);

        $this->invalidVariantGroupTypeId = $this->retrieveInvalidVariantGroupTypeId($pim);
    }

    private function retrieveInvalidVariantGroupTypeId(DestinationPim $pim): ?int
    {
        $result = $this->console->execute(
            new MySqlQueryCommand('SELECT id FROM pim_catalog_group_type WHERE code = "INVALID_VARIANT"'
        ), $pim)->getOutput();

        return isset($result[0]['id']) ? (int) $result[0]['id'] : null;
    }
}
