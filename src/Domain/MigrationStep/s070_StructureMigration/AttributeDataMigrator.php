<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Attribute table migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeDataMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $tableMigrator;

    /** @var ChainedConsole */
    private $chainedConsole;

    public function __construct(TableMigrator $naiveMigrator, ChainedConsole $chainedConsole)
    {
        $this->tableMigrator = $naiveMigrator;
        $this->chainedConsole = $chainedConsole;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $tableName = 'pim_catalog_attribute';

        $sqlUpdate = 'UPDATE %s SET backend_type = "%s" WHERE backend_type = "%s"';

        try {
            $this->tableMigrator->migrate($sourcePim, $destinationPim, $tableName);

            $this->chainedConsole->execute(
                new MySqlExecuteCommand(sprintf($sqlUpdate, $tableName, 'textarea', 'text')), $destinationPim
            );

            $this->chainedConsole->execute(
                new MySqlExecuteCommand(sprintf($sqlUpdate, $tableName, 'text', 'varchar')), $destinationPim
            );
        } catch (\Exception $exception) {
            throw new StructureMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
