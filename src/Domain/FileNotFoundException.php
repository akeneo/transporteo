<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

use Throwable;

/**
 * Thrown when a file is not found.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FileNotFoundException extends \Exception
{
    /** @var string */
    private $filePath;

    public function __construct($message = '', $filePath = '', $code = 0, Throwable $previous = null)
    {
        $this->filePath = $filePath;

        parent::__construct($message, $code, $previous);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
