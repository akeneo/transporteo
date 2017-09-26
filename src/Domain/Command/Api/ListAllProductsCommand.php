<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to list all the products from a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ListAllProductsCommand implements ApiCommand
{
    /** @var int */
    private $pageSize;

    public function __construct(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    public function getCommand(): string
    {
        return self::class;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
