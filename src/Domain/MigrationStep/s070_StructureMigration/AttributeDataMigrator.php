<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
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

    /** @var ConsoleHelper */
    private $consoleHelper;

    public function __construct(TableMigrator $naiveMigrator, ConsoleHelper $consoleHelper)
    {
        $this->tableMigrator = $naiveMigrator;
        $this->consoleHelper = $consoleHelper;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $tableName = 'pim_catalog_attribute';

        $sqlUpdate = 'UPDATE %s.%s SET backend_type = "%s" WHERE backend_type = "%s"';

        try {
            $this->tableMigrator->migrate($sourcePim, $destinationPim, $tableName);

            $this->consoleHelper->execute(
                $destinationPim,
                new MySqlExecuteCommand(sprintf($sqlUpdate, $destinationPim->getDatabaseName(), $tableName, 'textarea', 'text'))
            );

            $this->consoleHelper->execute(
                $destinationPim,
                new MySqlExecuteCommand(sprintf($sqlUpdate, $destinationPim->getDatabaseName(), $tableName, 'text', 'varchar'))
            );
        } catch (\Exception $exception) {
            throw new StructureMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
