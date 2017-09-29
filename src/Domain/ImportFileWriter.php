<?php

namespace Akeneo\PimMigration\Domain;

/**
 * Writes a import file.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface ImportFileWriter
{
    /**
     * Creates an empty file (even if it already exists) and write the data in it.
     */
    public function write(array $data, string $filePath): void;
}
