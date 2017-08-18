<?php

declare(strict_types=1);

namespace integration\Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Domain\StructureMigration\AttributeDataMigrator;
use Akeneo\PimMigration\Infrastructure\Command\LocalCommandLauncherFactory;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\DumpTableMigrator;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\MySqlQueryExecutor;
use Ds\Vector;
use integration\Akeneo\PimMigration\DatabaseSetupedTestCase;
use PHPUnit\Framework\TestCase;

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

        $this->dumpTableMigrator = new DumpTableMigrator(new LocalCommandLauncherFactory());
        $this->dumpTableMigrator->migrate($this->sourcePim, $this->destinationPim, 'pim_catalog_attribute_group');
    }

    public function testAttributeDataMigrator()
    {
        $attributeDataMigrator = new AttributeDataMigrator($this->dumpTableMigrator, new MySqlQueryExecutor());
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
