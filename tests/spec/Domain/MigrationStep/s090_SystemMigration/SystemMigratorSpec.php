<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration\SystemMigrationException;
use Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration\SystemMigrator;
use PhpSpec\ObjectBehavior;

/**
 * System Migrator Spec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SystemMigratorSpec extends ObjectBehavior
{
    public function let(ChainedConsole $chainedConsole)
    {
        $this->beConstructedWith($chainedConsole);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SystemMigrator::class);
    }

    public function it_migrates_system_successfully(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $chainedConsole
    ) {
        $this->addSystemMigrator($migratorOne);
        $this->addSystemMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $destinationPim->getDatabaseName()->willReturn('database_name');
        $chainedConsole
            ->execute(new MySqlExecuteCommand(
                'ALTER TABLE database_name.pim_api_access_token
                ADD COLUMN client int(11) DEFAULT NULL AFTER id,
                ADD CONSTRAINT FK_BD5E4023C7440455 FOREIGN KEY (client) REFERENCES database_name.pim_api_client (id) ON DELETE CASCADE;'
            ),
                $destinationPim)
            ->shouldBeCalled();

        $chainedConsole
            ->execute(new MySqlExecuteCommand(
                'CREATE INDEX IDX_BD5E4023C7440455 ON database_name.pim_api_access_token (client);'
            ), $destinationPim)->shouldBeCalled();

        $chainedConsole->execute(
            new MySqlExecuteCommand('UPDATE database_name.pim_api_client SET label = id WHERE label IS NULL;'),
            $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_throws_an_exception(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->addSystemMigrator($migratorOne);
        $this->addSystemMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->willThrow(new DataMigrationException());

        $this->shouldThrow(new SystemMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }
}
