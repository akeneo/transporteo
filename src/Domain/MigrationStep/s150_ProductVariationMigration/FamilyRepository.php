<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Repository for family data on the destination PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var array */
    private $familyCache = [];

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    public function findByCode(string $familyCode, Pim $pim): Family
    {
        if (isset($this->familyCache[$familyCode])) {
            return $this->familyCache[$familyCode];
        }

        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_family WHERE code = "%s"',
            $familyCode
        )), $pim)->getOutput();

        if (!isset($sqlResult[0]['id'])) {
            throw new \RuntimeException('Failed to find the family '.$familyCode);
        }

        $familyData = $this->console->execute(new GetFamilyCommand($familyCode), $pim)->getOutput();
        $family = new Family((int) $sqlResult[0]['id'], $familyCode, $familyData);
        $this->familyCache[$familyCode] = $family;

        return $family;
    }

    public function findById(int $familyId, Pim $pim): Family
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id, code FROM pim_catalog_family WHERE id = %d',
            $familyId
        )), $pim)->getOutput();

        if (!isset($sqlResult[0]['code'])) {
            throw new \RuntimeException('Failed to find the family '.$familyId);
        }

        $familyData = $this->console->execute(new GetFamilyCommand($sqlResult[0]['code']), $pim)->getOutput();
        $family = new Family((int) $sqlResult[0]['id'], $sqlResult[0]['code'], $familyData);

        return $family;
    }
}
