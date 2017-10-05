<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to delete a product.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DeleteProductCommand implements ApiCommand
{
    /** @var string */
    private $productCode;

    public function __construct(string $productCode)
    {
        $this->productCode = $productCode;
    }

    public function getCommand(): string
    {
        return self::class;
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }
}
