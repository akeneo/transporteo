<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to get an attribute from a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GetAttributeCommand implements ApiCommand
{
    /** @var string */
    private $attributeCode;

    public function __construct(string $attributeCode)
    {
        $this->attributeCode = $attributeCode;
    }

    public function getCommand(): string
    {
        return 'Get attribute '.$this->attributeCode;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
