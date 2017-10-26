<?php

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s160_ProductDraftMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use PhpSpec\ObjectBehavior;

class ProductDraftImporterSpec extends ObjectBehavior
{
    function let(ChainedConsole $console)
    {
        $this->beConstructedWith($console, '/../../../../tests/resources');
    }

    function it_import_product_draft(
        DestinationPim $destinationPim,
        CommandResult $commandResult,
        $console
    ) {
        $fileName = 'draft_59f07f67c6f92.csv';

        $console->execute(
            new MySqlQueryCommand('SELECT id FROM `pim_catalog_product` WHERE identifier = "AKNTS_BPM"'),
            $destinationPim
        )->willReturn($commandResult);

        $commandResult->getOutput()->willReturn([['id' => 1]]);

        $console->execute(
            new MySqlExecuteCommand("INSERT INTO `pimee_workflow_product_draft` (product_id, created_at, changes, status, author) VALUES (1, '2017-10-16 10:55:03', '{\"values\":{\"description\":[{\"locale\":\"en_US\",\"scope\":\"ecommerce\",\"data\":\"Hello\"}]},\"review_statuses\":{\"description\":[{\"locale\":\"en_US\",\"scope\":\"ecommerce\",\"status\":\"draft\"}]}}', 0, 'mary')"),
            $destinationPim
        )->shouldBeCalled();

        $this->import($destinationPim, $fileName);
    }
}
