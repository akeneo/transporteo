<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s150_ProductVariationMigration;

use Akeneo\PimMigration\Domain\MigrationStep\MigrationStepException;

/**
 * Exception thrown when some product variations can't not be migrated.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class InvalidProductVariationException extends MigrationStepException
{
    /** @var array */
    private $messages;

    public function __construct(array $messages)
    {
        $this->messages = $messages;

        parent::__construct(implode(PHP_EOL, $messages));
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
