<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to insert or update a list of products.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class UpsertListProductsCommand implements ApiCommand
{
    /** @var array */
    private $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function getCommand(): string
    {
        return self::class;
    }

    public function getProducts(): array
    {
        return $this->products;
    }
}
