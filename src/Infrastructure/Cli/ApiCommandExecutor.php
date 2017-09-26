<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\Api\ListAllProductsCommand;
use Akeneo\PimMigration\Domain\Command\Api\UpsertListProductsCommand;
use Akeneo\PimMigration\Domain\Command\ApiCommand;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimApiClientBuilder;

/**
 * Executes an API command.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ApiCommandExecutor
{
    /** @var PimApiClientBuilder */
    private $apiClientBuilder;

    public function __construct(PimApiClientBuilder $apiClientBuilder)
    {
        $this->apiClientBuilder = $apiClientBuilder;
    }

    /**
     * Executes an API command on a given PIM.
     */
    public function execute(ApiCommand $command, Pim $pim)
    {
        $apiClient = $this->apiClientBuilder->build($pim->getApiParameters());

        // TODO: do a registry if they are more commands
        if ($command instanceof ListAllProductsCommand) {
            $apiResult = $apiClient->getProductApi()->all($command->getPageSize());

            return new CommandResult(1, $apiResult);
        }

        if ($command instanceof UpsertListProductsCommand) {
            $apiResult = $apiClient->getProductApi()->upsertList($command->getProducts());

            return new CommandResult(1, $apiResult);
        }

        throw new \RuntimeException(sprintf('ApiCommand of type %S is not supported', get_class($command)));
    }
}
