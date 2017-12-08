<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\MixedVariation;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\Exception\InvalidMixedVariationException;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\FamilyVariantRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\InnerVariation\InnerVariationTypeRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupCombinationRepository;
use Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration\VariantGroup\VariantGroupRepository;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Psr\Log\LoggerInterface;

/**
 * Migration for products having variations through variant-group and IVB both.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class MixedVariationMigrator implements DataMigrator
{
    /** @var FamilyVariantBuilder */
    private $familyVariantBuilder;

    /** @var MixedVariationProductMigrator */
    private $productMigrator;

    /** @var InnerVariationTypeRepository */
    private $innerVariationTypeRepository;

    /** @var FamilyVariantRepository */
    private $familyVariantRepository;

    /** @var MixedVariationBuilder */
    private $mixedVariationBuilder;

    /** @var MixedVariationValidator */
    private $mixedVariationValidator;

    /** @var VariantGroupRepository */
    private $variantGroupRepository;

    /** @var VariantGroupCombinationRepository */
    private $variantGroupCombinationRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FamilyVariantBuilder $familyVariantBuilder,
        FamilyVariantRepository $familyVariantRepository,
        MixedVariationProductMigrator $productMigrator,
        InnerVariationTypeRepository $innerVariationTypeRepository,
        MixedVariationBuilder $mixedVariationBuilder,
        MixedVariationValidator $mixedVariationValidator,
        VariantGroupRepository $variantGroupRepository,
        VariantGroupCombinationRepository $variantGroupCombinationRepository,
        LoggerInterface $logger
    ) {
        $this->familyVariantBuilder = $familyVariantBuilder;
        $this->productMigrator = $productMigrator;
        $this->innerVariationTypeRepository = $innerVariationTypeRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->mixedVariationBuilder = $mixedVariationBuilder;
        $this->mixedVariationValidator = $mixedVariationValidator;
        $this->variantGroupRepository = $variantGroupRepository;
        $this->variantGroupCombinationRepository = $variantGroupCombinationRepository;
        $this->logger = $logger;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        $variantGroupCombinations = $this->variantGroupCombinationRepository->findAll($destinationPim);
        $hasInvalidMixedVariations = false;

        foreach ($variantGroupCombinations as $variantGroupCombination) {
            $mixedVariation = $this->mixedVariationBuilder->buildFromVariantGroupCombination($variantGroupCombination, $destinationPim);

            if (null === $mixedVariation) {
                continue;
            }

            if ($this->mixedVariationValidator->isValid($mixedVariation, $destinationPim)) {
                $this->migrateMixedVariation($mixedVariation, $destinationPim);
            } else {
                $hasInvalidMixedVariations = true;
            }

            $this->deleteMixedVariation($mixedVariation, $destinationPim);
        }

        if ($hasInvalidMixedVariations) {
            $this->logger->warning(<<<EOT
There are mixed variant groups and inner variation types that can't be automatically migrated. Related products have been migrated but they're not variant.
Your catalog structure should be rework, according to the catalog modeling introduced in v2.0
EOT
            );
        }
    }

    private function migrateMixedVariation(MixedVariation $mixedVariation, DestinationPim $pim): void
    {
        $familyVariant = $this->familyVariantBuilder->build($mixedVariation, $pim);
        $familyVariant = $this->familyVariantRepository->persist($familyVariant, $pim);

        $this->productMigrator->migrateProducts($mixedVariation, $familyVariant, $pim);
    }

    private function deleteMixedVariation(MixedVariation $mixedVariation, DestinationPim $pim): void
    {
        $this->innerVariationTypeRepository->delete($mixedVariation->getInnerVariationType(), $pim);

        foreach ($mixedVariation->getVariantGroupCombination()->getGroups() as $variantGroupCode) {
            $this->variantGroupRepository->removeSoftlyVariantGroup($variantGroupCode, $pim);
        }
    }
}
