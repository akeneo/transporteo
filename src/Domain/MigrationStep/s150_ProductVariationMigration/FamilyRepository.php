<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Repository for family data.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var FamilyVariantImporter */
    private $familyVariantImporter;

    public function __construct(ChainedConsole $console, FamilyVariantImporter $familyVariantImporter)
    {
        $this->console = $console;
        $this->familyVariantImporter = $familyVariantImporter;
    }

    public function createFamilyVariant($familyVariantData, DestinationPim $pim): void
    {
        $this->familyVariantImporter->import([$familyVariantData], $pim);
    }

        public function findByCode(string $familyCode, Pim $pim): Family
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_family WHERE code = "%s"',
            $familyCode
        )), $pim)->getOutput();

        if (!isset($sqlResult[0]['id'])) {
            throw new \RuntimeException('Failed to find the family '.$familyCode);
        }

        $familyData = $this->console->execute(new GetFamilyCommand($familyCode), $pim)->getOutput();

        return new Family((int) $sqlResult[0]['id'], $familyCode, $familyData);
    }
}
