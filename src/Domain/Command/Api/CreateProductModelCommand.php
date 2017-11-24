<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CreateProductModelCommand implements ApiCommand
{
    /** @var string */
    private $code;

    /** @var array */
    private $data;

    public function __construct($code, array $data)
    {
        $this->code = $code;
        $this->data = $data;
    }

    public function getCommand(): string
    {
        return 'Create product model ' . $this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
