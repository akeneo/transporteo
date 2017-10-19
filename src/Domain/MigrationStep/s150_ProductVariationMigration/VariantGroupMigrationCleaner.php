<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Psr\Log\LoggerInterface;

/**
 * Cleans the data of the destination PIM after the migration of the variant groups.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class VariantGroupMigrationCleaner
{
    /** @var ChainedConsole */
    private $console;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ChainedConsole $console, LoggerInterface $logger)
    {
        $this->console = $console;
        $this->logger = $logger;
    }

    /**
     * Drops the columns and tables that should no longer exists in the destination PIM.
     * Deletes the variant-group that have been migrated.
     */
    public function clean(DestinationPim $pim)
    {
        $this->removeProductTemplateForeignKey($pim);
        $this->removeProductTemplateColumn($pim);

        $this->console->execute(new MySqlExecuteCommand(
            'DROP TABLE pim_catalog_group_attribute, pim_catalog_product_template'
        ), $pim);

        $this->console->execute(new MySqlExecuteCommand(
            "DELETE g FROM pim_catalog_group g
            INNER JOIN pim_catalog_group_type gt ON gt.id = g.type_id
            WHERE gt.code = 'VARIANT'"
        ), $pim);

        $this->console->execute(new MySqlExecuteCommand(
            "DELETE FROM pim_catalog_group_type WHERE code = 'VARIANT'"
        ), $pim);
    }

    private function removeProductTemplateForeignKey(DestinationPim $pim): void
    {
        $foreignKey = $this->console->execute(new MySqlQueryCommand(sprintf(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = 'pim_catalog_group' AND COLUMN_NAME = 'product_template_id'
             AND REFERENCED_TABLE_NAME = 'pim_catalog_product_template'",
            $pim->getDatabaseName()
        )), $pim)->getOutput();

        if (empty($foreignKey)) {
            $this->logger->warning('Unable to find the foreign key on pim_catalog_product_template to drop the table pim_catalog_group');
        } else {
            $this->console->execute(new MySqlExecuteCommand(sprintf(
                'ALTER TABLE pim_catalog_group DROP FOREIGN KEY `%s`',
                $foreignKey[0]['CONSTRAINT_NAME']
            )), $pim);
        }
    }

    private function removeProductTemplateColumn(DestinationPim $pim): void
    {
        $query = "ALTER TABLE pim_catalog_group DROP COLUMN product_template_id";

        $index = $this->console->execute(new MySqlQueryCommand(
            "SHOW index FROM pim_catalog_group WHERE Column_name = 'product_template_id'"
        ), $pim)->getOutput();

        if (empty($index)) {
            $this->logger->warning('Unable to find the index on column product_template_id in table pim_catalog_group');
        } else {
            $query .= sprintf(", DROP INDEX `%s`", $index[0]['Key_name']);
        }

        $this->console->execute(new MySqlExecuteCommand($query), $pim);
    }
}
