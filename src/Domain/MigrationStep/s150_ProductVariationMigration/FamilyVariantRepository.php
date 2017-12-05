<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\Api\CreateFamilyVariantCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\ProductVariationMigrationException;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Repository for family variant.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantRepository
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

    public function persist(FamilyVariant $familyVariant, DestinationPim $pim): FamilyVariant
    {
        $familyVariantData = [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'attributes' => $familyVariant->getLevelOneAttributes(),
                    'axes' => $familyVariant->getLevelOneAxes()
                ]
            ],
            'labels' => $familyVariant->getLabels()
        ];

        if (!empty($familyVariant->getLevelTwoAxes())) {
            $familyVariantData['variant_attribute_sets'][] = [
                'level' => 2,
                'attributes' => $familyVariant->getLevelTwoAttributes(),
                'axes' => $familyVariant->getLevelTwoAxes()
            ];
        }

        $this->console->execute(new CreateFamilyVariantCommand($familyVariant->getFamilyCode(), $familyVariant->getCode(), $familyVariantData), $pim);

        if (null === $familyVariant->getId()) {
            $id = $this->getFamilyVariantId($familyVariant->getCode(), $pim);

            $familyVariant = new FamilyVariant(
                $id,
                $familyVariant->getCode(),
                $familyVariant->getFamilyCode(),
                $familyVariant->getLevelOneAxes(),
                $familyVariant->getLevelTwoAxes(),
                $familyVariant->getLevelOneAttributes(),
                $familyVariant->getLevelTwoAttributes(),
                $familyVariant->getLabels()
            );
        }

        return $familyVariant;
    }

    public function findOneByCode(string $familyVariantCode, Pim $pim): FamilyVariant
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id, code FROM pim_catalog_family_variant WHERE code = "%s"',
            $familyVariantCode
        )), $pim)->getOutput();

        if (empty($sqlResult)) {
            throw new ProductVariationMigrationException(
                sprintf(
                    'Unable to find the family variant "%s" from parent family.',
                    $familyVariantCode
                )
            );
        }

        return new FamilyVariant((int) $sqlResult[0]['id'], $sqlResult[0]['code']);
    }

    public function getFamilyVariantId(string $familyVariantCode, Pim $pim): int
    {
        $sqlResult = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT id FROM pim_catalog_family_variant WHERE code = "%s"',
            $familyVariantCode
        )), $pim)->getOutput();

        if(!isset($sqlResult[0]['id'])) {
            throw new ProductVariationMigrationException(
                sprintf(
                    'Unable to retrieve the family variant %s. It seems that its creation failed.',
                    $familyVariantCode
                )
            );
        }

        return (int) $sqlResult[0]['id'];
    }
}
