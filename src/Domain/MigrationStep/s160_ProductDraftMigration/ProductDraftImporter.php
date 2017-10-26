<?php

namespace Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftImporter
{
    /** @var ChainedConsole */
    private $console;

    /** @var string */
    private $varDir;

    public function __construct(ChainedConsole $console, string $varDir)
    {
        $this->console = $console;
        $this->varDir = __DIR__ . $varDir;
    }

    public function import(DestinationPim $pim, string $draftFilename)
    {
        $reader = ReaderFactory::create(Type::CSV);
        $reader->open($this->varDir .'/'. $draftFilename);

        foreach ($reader->getSheetIterator() as $sheet) {
            $hasHeader = true;
            foreach ($sheet->getRowIterator() as $row) {
                if (!$hasHeader) {
                    $sqlSelectIdQuery = 'SELECT id FROM `pim_catalog_product` WHERE identifier = "%s"';
                    $id = $this->console->execute(new MySqlQueryCommand(sprintf($sqlSelectIdQuery, $row[0])), $pim)->getOutput()[0]['id'];

                    $sqlInsertDraftQuery = "INSERT INTO `pimee_workflow_product_draft` (product_id, created_at, changes, status, author) VALUES (%s, '%s', '%s', %s, '%s')";
                    $this->console->execute(
                        new MySqlExecuteCommand(
                            sprintf($sqlInsertDraftQuery, $id, $row[1], $row[2], $row[3], $row[4])
                        ),
                        $pim
                    );
                }
                $hasHeader = false;
            }
        }
    }
}
