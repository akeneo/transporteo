<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\DataMigration\QueryException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Aims to retrieve data related to the migration of the inner variation types.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationTypeRepository
{
    /** @var ChainedConsole */
    private $console;

    /** @var FamilyRepository */
    private $familyRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ChainedConsole $console, FamilyRepository $familyRepository, LoggerInterface $logger)
    {
        $this->console = $console;
        $this->familyRepository = $familyRepository;
        $this->logger = $logger;
    }

    /**
     * Return array of all InnerVariationType occurrences of a PIM.
     */
    public function findAll(Pim $pim): array
    {
        $innerVariationTypesData = $innerVariationTables = $this->console->execute(
            new MySqlQueryCommand('SELECT id, code, variation_family_id FROM pim_inner_variation_inner_variation_type'),
            $pim
        )->getOutput();

        $innerVariationTypes = [];

        foreach ($innerVariationTypesData as $innerVariationTypeData) {
            $id = (int) $innerVariationTypeData['id'];
            $variationFamilyId = $this->familyRepository->findById((int) $innerVariationTypeData['variation_family_id'], $pim);

            $innerVariationTypes[] = new InnerVariationType(
                $id,
                $innerVariationTypeData['code'],
                $variationFamilyId,
                $this->getAxes($id, $pim)
            );
        }

        return $innerVariationTypes;
    }

    public function delete(InnerVariationType $innerVariationType, DestinationPim $pim): void
    {
        $this->console->execute(new MySqlExecuteCommand(sprintf(
            'DELETE FROM pim_inner_variation_inner_variation_type_family WHERE inner_variation_type_id = %d',
            $innerVariationType->getId()
        )), $pim);

        $this->console->execute(new MySqlExecuteCommand(sprintf(
            'DELETE FROM pim_inner_variation_inner_variation_type WHERE id = %d',
            $innerVariationType->getId()
        )), $pim);

        // Try to delete the variation family is its not related to another inner variation type.
        try {
            $this->console->execute(new MySqlExecuteCommand(sprintf(
                'DELETE FROM pim_catalog_family WHERE id = %d',
                $innerVariationType->getVariationFamilyId()
            )), $pim);
        } catch (QueryException $e) {
            $this->logger->debug(sprintf(
                'Failed to delete the variation family %s of the inner variation type %s',
                $innerVariationType->getVariationFamilyId(),
                $innerVariationType->getCode()
            ));
        }
    }

    /**
     * Retrieves parent families having variant products related to an InnerVariationType.
     */
    public function getParentFamiliesHavingVariantProducts(InnerVariationType $innerVariationType, Pim $pim): \Traversable
    {
        $parentFamiliesData = $this->console->execute(
            new MySqlQueryCommand(sprintf(
                'SELECT DISTINCT f.code, f.id
                 FROM pim_inner_variation_inner_variation_type ivt
                 INNER JOIN pim_inner_variation_inner_variation_type_family ivtf ON ivtf.inner_variation_type_id = ivt.id
                 INNER JOIN pim_catalog_family f ON f.id = ivtf.family_id
                 INNER JOIN pim_catalog_product product_model ON product_model.family_id = f.id
                 WHERE ivt.id = %d
                  AND EXISTS(
                     SELECT * FROM pim_catalog_product AS product_variant
                     WHERE product_variant.family_id = ivt.variation_family_id
                     AND JSON_EXTRACT(product_variant.raw_values, \'$.variation_parent_product."<all_channels>"."<all_locales>"\') = product_model.identifier
                 )',
                 $innerVariationType->getId()
        )), $pim
        )->getOutput();

        foreach ($parentFamiliesData as $parentFamilyData) {
            yield $this->buildFamily((int) $parentFamilyData['id'], $parentFamilyData['code'], $pim);
        }
    }

    /**
     * Retrieves the label of an InnerVariationType for a given locale.
     */
    public function getLabel(InnerVariationType $innerVariationType, string $locale, Pim $pim): string
    {
        $innerVariationTypeLabel = $this->console->execute(new MySqlQueryCommand(sprintf(
            'SELECT label FROM pim_inner_variation_inner_variation_type_translation
            WHERE foreign_key = %d AND locale = "%s"',
            $innerVariationType->getId(),
            $locale
        )), $pim)->getOutput();

        return $innerVariationTypeLabel[0]['label'] ?? '';
    }

    public function findOneForFamilyCode(string $familyCode, Pim $pim): ?InnerVariationType
    {
        $innerVariationTypeData = $this->console->execute(
            new MySqlQueryCommand(sprintf(
                'SELECT ivt.id, ivt.code, ivt.variation_family_id 
                FROM pim_inner_variation_inner_variation_type ivt
                INNER JOIN pim_inner_variation_inner_variation_type_family ivtf ON ivtf.inner_variation_type_id = ivt.id
                INNER JOIN pim_catalog_family f ON f.id = ivtf.family_id
                WHERE f.code = "%s"'
                , $familyCode)),
            $pim
        )->getOutput();

        if (empty($innerVariationTypeData)) {
            return null;
        }

        $innerVariationTypeData = $innerVariationTypeData[0];
        $innerVariationTypeId = (int) $innerVariationTypeData['id'];

        return new InnerVariationType(
            $innerVariationTypeId,
            $innerVariationTypeData['code'],
            $this->familyRepository->findById((int) $innerVariationTypeData['variation_family_id'], $pim),
            $this->getAxes($innerVariationTypeId, $pim)
        );
    }

    /**
     * Retrieves the axes data of a given InnerVariationType id.
     */
    private function getAxes(int $innerVariationTypeId, Pim $pim): array
    {
        return $this->console->execute(
            new MySqlQueryCommand(
                'SELECT code, attribute_type FROM pim_inner_variation_inner_variation_type_axis
                INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id = attribute_id
                WHERE inner_variation_type_id = '.$innerVariationTypeId
            ),
            $pim
        )->getOutput();
    }

    /**
     * Retrieves all the data of a family.
     */
    private function buildFamily(int $familyId, string $familyCode, Pim $pim): Family
    {
        $apiCommand = new GetFamilyCommand($familyCode);
        $familyStandardData = $this->console->execute($apiCommand, $pim)->getOutput();

        return new Family($familyId, $familyCode, $familyStandardData);
    }
}
