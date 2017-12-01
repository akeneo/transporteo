<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Retrieves an builds the variant group combinations.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupCombinationRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var FamilyRepository */
    private $familyRepository;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    public function __construct(ChainedConsole $console, FamilyRepository $familyRepository, VariantGroupRepository $variantGroupRepository)
    {
        $this->console = $console;
        $this->familyRepository = $familyRepository;
        $this->variantGroupRepository = $variantGroupRepository;
    }

    public function findAll(DestinationPim $pim): \Traversable
    {
        $query = <<<SQL
SELECT f.code as family_code, f.id as family_id,
(
	SELECT GROUP_CONCAT(DISTINCT a.code ORDER BY a.code ASC SEPARATOR ',')
	FROM pim_catalog_group_attribute axis
	INNER JOIN pim_catalog_attribute a ON axis.attribute_id = a.id
	WHERE axis.group_id = g.id
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

        $results = $this->console->execute(new MySqlQueryCommand($query), $pim)->getOutput();

        foreach ($results as $result) {
            yield $this->buildVariantGroupCombination($result, $pim);
        }
    }

    public function removeSoftly(VariantGroupCombination $variantGroupCombination, DestinationPim $pim): void
    {
        foreach ($variantGroupCombination->getGroups() as $groupCode) {
            $this->variantGroupRepository->removeSoftlyVariantGroup($groupCode, $pim);
        }
    }

    private function buildVariantGroupCombination(array $rawData, DestinationPim $pim): VariantGroupCombination
    {
        $family = $this->familyRepository->findByCode($rawData['family_code'], $pim);

        $groups = explode(',', $rawData['groups']);
        $attributes = $this->variantGroupRepository->retrieveGroupAttributes($groups[0], $pim);

        return new VariantGroupCombination(
            $family,
            explode(',', $rawData['axes']),
            $groups,
            $attributes
        );
    }
}
