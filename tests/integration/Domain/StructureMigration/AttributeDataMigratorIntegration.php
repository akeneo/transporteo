<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\AttributeDataMigrator;
use Akeneo\PimMigration\Infrastructure\LocalFileFetcher;
use Akeneo\PimMigration\Infrastructure\Pim\Localhost;
use Ds\Vector;
use integration\Akeneo\PimMigration\DatabaseSetupedTestCase;

/**
 * Integration test for Attribute Data Migrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeDataMigratorIntegration extends DatabaseSetupedTestCase
{
    private $dumpTableMigrator;

    public function setUp()
    {
        parent::setUp();

        $fileFetcherRegistry = new FileFetcherRegistry();
        $fileFetcherRegistry->addFileFetcher(new LocalFileFetcher(new FileSystemHelper()));
        $fileFetcherRegistry->connectSourcePim(new Localhost());
        $fileFetcherRegistry->connectDestinationPim(new Localhost());

        $tableMigrator = new TableMigrator($this->databaseQueryExectuorRegistry, $fileFetcherRegistry);

        $this->dumpTableMigrator = $tableMigrator;
        $this->dumpTableMigrator->migrate($this->sourcePim, $this->destinationPim, 'pim_catalog_attribute_group');
    }

    public function testAttributeDataMigrator()
    {
        $attributeDataMigrator = new AttributeDataMigrator($this->dumpTableMigrator, $this->databaseQueryExectuorRegistry);
        $containingTextElementsInSource = $this->getConnection($this->sourcePim, true)->query('SELECT id FROM pim_catalog_attribute WHERE backend_type="text"')->fetchAll();
        $idsOldText = (new Vector($containingTextElementsInSource))->map(function (array $values) {
            return $values['id'];
        })->toArray();


        $attributeDataMigrator->migrate($this->sourcePim, $this->destinationPim);

        $oldTextBackendTypes = $this->getConnection($this->destinationPim, true)->query(sprintf('SELECT backend_type from pim_catalog_attribute WHERE id IN (%s)', implode(', ', $idsOldText)))->fetchAll();

        foreach ($oldTextBackendTypes as $oldTextBackendType) {
            $this->assertEquals($oldTextBackendType['backend_type'], 'textarea');
        }
    }
}
