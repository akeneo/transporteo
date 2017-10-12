<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to update a family on a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class UpdateFamilyCommand implements ApiCommand
{
    /** @var array */
    private $family;

    public function __construct(array $family)
    {
        $this->family = $family;
    }

    public function getCommand(): string
    {
        return 'Update family '.$this->getFamilyCode();
    }

    public function getFamily(): array
    {
        return $this->family;
    }

    public function getFamilyCode(): string
    {
        return $this->family['code'];
    }
}
