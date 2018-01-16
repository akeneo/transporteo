<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation;

use Akeneo\PimMigration\Domain\Command\Api\UpdateFamilyCommand;
use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\Family;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\FamilyVariant;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Entity\InnerVariationType;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantImporter;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Psr\Log\LoggerInterface;

/**
 * Migrates the families data of an InnerVariationType.
 *  - Copy the attributes of the IVB variation family to the parent family
 *  - Create the real families variants.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InnerVariationFamilyMigrator
{
    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var FamilyVariantImporter */
    private $familyVariantImporter;

    /** @var LoggerInterface */
    private $logger;

    /** @var ChainedConsole */
    private $console;

    /** @var FamilyVariantRepository */
    private $familyVariantRepository;

    /** @var FamilyRepository */
    private $familyRepository;

    public function __construct(
        InnerVariationTypeRepository $innerVariationTypeRepository,
        FamilyVariantImporter $familyVariantImporter,
        ChainedConsole $console,
        LoggerInterface $logger,
        FamilyVariantRepository $familyVariantRepository,
        FamilyRepository $familyRepository
    ) {
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->familyVariantImporter = $familyVariantImporter;
        $this->logger = $logger;
        $this->console = $console;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->familyRepository = $familyRepository;
    }

    public function migrate(InnerVariationType $innerVariationType, Pim $pim): void
    {
        $innerVariationFamily = $innerVariationType->getVariationFamily();
        $parentFamilies = $this->familyRepository->findAllByInnerVariationType($innerVariationType, $pim);

        foreach ($parentFamilies as $parentFamily) {
            $this->migrateFamilyAttributes($parentFamily, $innerVariationFamily, $pim);

            $familyVariant = $this->buildFamilyVariant($parentFamily, $innerVariationFamily, $innerVariationType, $pim);
            $this->familyVariantRepository->persist($familyVariant, $pim);
        }
    }

    /**
     * Creates a family variant from an InnerVariationType.
     */
    private function buildFamilyVariant(
        Family $parentFamily,
        Family $innerVariationFamily,
        InnerVariationType $innerVariationType,
        Pim $pim
    ): FamilyVariant
    {
        $axesCodes = [];
        foreach ($innerVariationType->getAxes() as $axis) {
            $axesCodes[] = $axis['code'];
        }

        $parentFamilyData = $parentFamily->getStandardData();
        $innerVariationFamilyData = $innerVariationFamily->getStandardData();
        $attributes = $this->removeVariationParentProductAttribute($innerVariationFamilyData['attributes']);

        $labels = [];
        foreach ($parentFamilyData['labels'] as $locale => $label) {
            $innerVariationLabel = $this->innerVariationTypeRepository->getLabel($innerVariationType, $locale, $pim);
            $labels[$locale] = $label.' '.$innerVariationLabel;
        }

        return new FamilyVariant(
            null,
            $parentFamily->getCode(),
            $parentFamily->getCode(),
            $axesCodes,
            [],
            $attributes,
            [],
            $labels
        );
    }

    /**
     * Adds the attributes (and their requirements) of a "variant" family into a "parent" family.
     */
    public function migrateFamilyAttributes(Family $parentFamily, Family $familyVariant, Pim $pim): void
    {
        $this->logger->debug(sprintf('Add the attributes of family %s to family %s', $familyVariant->getCode(), $parentFamily->getCode()));

        $parentFamilyData = $parentFamily->getStandardData();
        $familyVariantData = $familyVariant->getStandardData();

        $familyVariantData['attributes'] = $this->removeVariationParentProductAttribute($familyVariantData['attributes']);

        $attributesToAdd = array_diff($familyVariantData['attributes'], $parentFamilyData['attributes']);
        $parentFamilyData['attributes'] = array_merge($parentFamilyData['attributes'], $attributesToAdd);

        foreach (array_keys($familyVariantData['attribute_requirements']) as $channel) {
            $familyVariantRequirements = $this->removeVariationParentProductAttribute($familyVariantData['attribute_requirements'][$channel]);
            $requirementsToAdd = array_diff($familyVariantRequirements, $parentFamilyData['attribute_requirements'][$channel]);
            $parentFamilyData['attribute_requirements'][$channel] = array_merge($parentFamilyData['attribute_requirements'][$channel], $requirementsToAdd);
        }

        try {
            $this->console->execute(new UpdateFamilyCommand($parentFamilyData), $pim);
        } catch (\Exception $exception) {
            $this->logger->warning(sprintf(
                'Unable to migrate the attributes of the variant family %s into the parent family %s : %s',
                $familyVariant->getCode(),
                $parentFamily->getCode(),
                $exception->getMessage()
            ));
        }
    }

    /**
     * Removes the attribute variation_parent_product from a list of attributes.
     */
    private function removeVariationParentProductAttribute(array $attributes): array
    {
        return array_diff($attributes, ['variation_parent_product']);
    }
}
