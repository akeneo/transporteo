<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\JobExecutionCommand;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\ImportFileWriter;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Imports products models into a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductModelImporter
{
    const JOB_CODE = 'migration_csv_product_model_import';
    const JOB_LABEL = 'CSV product model import for the migration';
    const FILE_PATH = '/tmp/migration_product_model_import.csv';

    /** @var ChainedConsole */
    private $console;

    /** @var ImportFileWriter */
    private $importFileWriter;

    public function __construct(ChainedConsole $console, ImportFileWriter $importFileWriter)
    {
        $this->console = $console;
        $this->importFileWriter = $importFileWriter;
    }

    public function import(array $productsModels, Pim $pim)
    {
        $formattedProducts = [];
        foreach ($productsModels as $productModel) {
            $formattedProducts[] = [
                'code' => $productModel['identifier'],
                'family_variant' => $productModel['family_variant'],
                'parent' => '',
            ];
        }

        $this->importFileWriter->write($formattedProducts, self::FILE_PATH);

        $this->ensureImportJobExists($pim);

        $this->console->execute(new JobExecutionCommand(self::JOB_CODE, []), $pim);
    }

    /**
     * Verifies that the import job exists, and create it if it's not.
     */
    private function ensureImportJobExists(Pim $pim): void
    {
        $commandResult = $this->console->execute(new SymfonyCommand('akeneo:batch:list-jobs -t import'), $pim);

        if (false === strpos($commandResult->getOutput(), self::JOB_CODE)) {
            $command = new SymfonyCommand(sprintf(
                "akeneo:batch:create-job 'Akeneo CSV Connector' csv_product_model_import import %s '%s' '%s'",
                self::JOB_CODE,
                json_encode([
                    'filePath' => self::FILE_PATH,
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'decimalSeparator' => '.',
                    'dateFormat' => 'yyyy-MM-dd',
                    'enabled' => true,
                    'categoriesColumn' => 'categories',
                    'familyVariantColumn' => 'family_variant',
                    'enabledComparison' => true,
                ]),
                self::JOB_LABEL
            ));

            $this->console->execute($command, $pim);
        }
    }
}
