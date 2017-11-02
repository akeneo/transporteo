<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;

/**
 * Removes a variant group and its related products.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupRemover
{
    /** @var ChainedConsole */
    private $console;

    /** @var int */
    private $typeId;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    /**
     * Removes softly a variant-group from the migration by changing their type to a specific one.
     */
    public function remove(string $variantGroupCode, DestinationPim $pim): void
    {
        if (null === $this->typeId) {
            $this->createMigrationGroupType($pim);
        }

        $query = sprintf(
            'UPDATE pim_catalog_group SET type_id = %d WHERE code = "%s"',
            $this->typeId,
            $variantGroupCode
        );

        $this->console->execute(new MySqlExecuteCommand($query), $pim);
    }

    private function createMigrationGroupType(DestinationPim $pim): void
    {
        $this->console->execute(new MySqlExecuteCommand('INSERT INTO pim_catalog_group_type SET code = "MVARIANT"'), $pim);

        $result = $this->console->execute(new MySqlQueryCommand('SELECT id FROM pim_catalog_group_type WHERE code = "MVARIANT"'), $pim);

        $this->typeId = (int) $result->getOutput()[0]['id'];
    }
}
