<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\PimMigration\Domain\Command\Api\CreateFamilyVariantCommand;
use Akeneo\PimMigration\Domain\Command\Api\CreateProductModelCommand;
use Akeneo\PimMigration\Domain\Command\Api\DeleteProductCommand;
use Akeneo\PimMigration\Domain\Command\Api\GetAttributeCommand;
use Akeneo\PimMigration\Domain\Command\Api\GetFamilyCommand;
use Akeneo\PimMigration\Domain\Command\Api\GetProductCommand;
use Akeneo\PimMigration\Domain\Command\Api\ListAllProductsCommand;
use Akeneo\PimMigration\Domain\Command\Api\UpdateFamilyCommand;
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
    public function execute(ApiCommand $command, Pim $pim): CommandResult
    {
        $apiClient = $this->apiClientBuilder->build($pim->getApiParameters());
        $apiResult = $this->executeCommandFromApiClient($command, $apiClient);

        return new CommandResult(1, $apiResult);
    }

    /**
     * Executes a command through an API client.
     */
    private function executeCommandFromApiClient(ApiCommand $command, AkeneoPimClientInterface $apiClient)
    {
        if ($command instanceof ListAllProductsCommand) {
            return $apiClient->getProductApi()->all($command->getPageSize());
        }

        if ($command instanceof GetProductCommand) {
            return $apiClient->getProductApi()->get($command->getCode());
        }

        if ($command instanceof UpsertListProductsCommand) {
            return $apiClient->getProductApi()->upsertList($command->getProducts());
        }

        if ($command instanceof DeleteProductCommand) {
            return $apiClient->getProductApi()->delete($command->getProductCode());
        }

        if ($command instanceof GetFamilyCommand) {
            return $apiClient->getFamilyApi()->get($command->getFamilyCode());
        }

        if ($command instanceof UpdateFamilyCommand) {
            return $apiClient->getFamilyApi()->upsert($command->getFamilyCode(), $command->getFamily());
        }

        if ($command instanceof CreateFamilyVariantCommand) {
            return $apiClient->getFamilyVariantApi()->create($command->getFamilyCode(), $command->getCode(), $command->getData());
        }

        if ($command instanceof GetAttributeCommand) {
            return $apiClient->getAttributeApi()->get($command->getAttributeCode());
        }

        if ($command instanceof CreateProductModelCommand) {
            return $apiClient->getProductModelApi()->create($command->getCode(), $command->getData());
        }

        throw new \RuntimeException(sprintf('ApiCommand of type %s is not supported', get_class($command)));
    }
}
