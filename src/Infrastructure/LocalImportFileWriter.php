<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\ImportFileWriter;

/**
 * Write a CSV import file locally.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalImportFileWriter implements ImportFileWriter
{
    /**
     * All the data must have the same keys.
     */
    public function write(array $data, string $filePath): void
    {
        $fileHandle = fopen($filePath, 'w');
        if (!is_resource($fileHandle)) {
            throw new \InvalidArgumentException('Unable to open the file '.$filePath);
        }

        if (empty($data)) {
            fclose($fileHandle);

            return;
        }

        fputcsv($fileHandle, array_keys($data[0]), ';');

        foreach ($data as $dataLine) {
            fputcsv($fileHandle, $dataLine, ';');
        }

        fclose($fileHandle);
    }
}
