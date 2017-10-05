<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\JobExecutionCommand;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\FileSystemHelper;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Imports families variants into a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FamilyVariantImporter
{
    const JOB_CODE = 'migration_csv_family_variant_import';
    const JOB_LABEL = 'CSV family variant import for the migration';
    const FILE_PATH = '/tmp/migration_family_variant_import.csv';

    /** @var ChainedConsole */
    private $console;

    /** @var FileSystemHelper */
    private $fileHelper;

    public function __construct(ChainedConsole $console, FileSystemHelper $fileHelper)
    {
        $this->console = $console;
        $this->fileHelper = $fileHelper;
    }

    /**
     * Launch a product import job for a given file.
     */
    public function import(array $families, Pim $pim): void
    {
        $this->fileHelper->writeImportFile($families, self::FILE_PATH);

        $this->ensureImportJobExists($pim);

        $this->console->execute(new JobExecutionCommand(self::JOB_CODE, []), $pim);
    }

    /**
     * Verifies that the import job exists, and create it if it's not.
     */
    private function ensureImportJobExists(Pim $pim): void
    {
        $commandResult = $this->console->execute(new SymfonyCommand('akeneo:batch:list-jobs -t import'), $pim);

        if (false === strpos($commandResult->getOutput(), static::JOB_CODE)) {
            $command = new SymfonyCommand(sprintf(
                "akeneo:batch:create-job 'Akeneo CSV Connector' csv_family_variant_import import %s '%s' '%s'",
                self::JOB_CODE,
                json_encode([
                    'filePath' => self::FILE_PATH,
                    'delimiter' => ';',
                    'enclosure' => '"',
                ]),
                self::JOB_LABEL
            ));

            $this->console->execute($command, $pim);
        }
    }
}
