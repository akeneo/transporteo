<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\ReferenceDataMigration\ReferenceDataMigrator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\BasicCommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandExecutor;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Reference Data Migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataMigratorIntegration extends TestCase
{
    public function testItParsesTheResultCommand()
    {
        $referenceDataExecutor = new ReferenceDataMigrator(new BasicCommandLauncher(new LocalCommandExecutor()));

        $sourcePim = new SourcePim('host', 12, 'a_base', 'a_user', 'a_password', null, null, false, null, false, '/home/anael/Developer/Akeneo/pim-community-standard');
        $destinationPim = new DestinationPim('host', 12, 'a_base', 'a_user', 'a_password', false, null, 'index_name', 'index_host', '/home/anael/Developer/Akeneo/pim-community-standard-20');

        $referenceDataExecutor->migrate($sourcePim, $destinationPim);
    }
}
