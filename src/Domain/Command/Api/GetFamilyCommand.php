<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command\Api;

use Akeneo\PimMigration\Domain\Command\ApiCommand;

/**
 * Command to get a family from a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GetFamilyCommand implements ApiCommand
{
    /** @var string */
    private $familyCode;

    public function __construct($familyCode)
    {
        $this->familyCode = $familyCode;
    }

    public function getCommand(): string
    {
        return 'Get family '.$this->familyCode;
    }

    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }
}
