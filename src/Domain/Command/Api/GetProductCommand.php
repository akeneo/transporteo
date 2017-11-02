<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to get a product.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GetProductCommand implements ApiCommand
{
    /** @var string */
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function getCommand(): string
    {
        return 'Get product '.$this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
