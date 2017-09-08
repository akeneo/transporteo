<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * A command is something we'll have to execute in order to migrate the PIM data or to retrieve information to help to migrate the PIM data. For instance, to be able to migrate the PIM we need to:
 * - execute a Symfony commands like `debug:container` {@see Akeneo\PimMigration\Domain\DataMigration\BundleConfigFetcher}
 * - execute regular Unix commands like `composer update` {@see Akeneo\PimMigration\Infrastructure\DestinationPimInstallation\DockerDestinationPimSystemRequirementsInstaller }
 * - execute a MySQL query command like `mysql {connectionInformation} `SHOW TABLES`` {@see Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration\ExtraDataMigrator}
 * - execute a MySQL dump command like `mysqldump {connectionInformation} TABLE_NAME > A_PATH` {@see Akeneo\PimMigration\Infrastructure\DatabaseServices\AbstractMysqlQueryExecutor}
 * - execute a MySQL raw command like `mysql {connectionInformation}  WHAT_YOU_WANT` {@see Akeneo\PimMigration\Infrastructure\DatabaseServices\AbstractMysqlQueryExecutor}.
 *
 * Commands are considered as part of the domain as we DO need them to migrate the PIM data. However, the way are launched (locally, over SSH, over Docker, on a Symfony 2.x vs Symfony 3.0) are part of the infrastructure ({@see Akeneo\PimMigration\Infrastructure\Cli\DockerConsole}).
 *
 * To be clear, let's imagine we need the Symfony command `foo:bar` in our migration process.  Inside the in the domain layer, we don't care if ultimately the command is launched via:
 * - ssh /path/to/pim/app/console foo:bar
 * - /path/to/pim/bin/console foo:bar
 * - docker bin/console foo:bar
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface Command
{
    public function getCommand(): string;
}
