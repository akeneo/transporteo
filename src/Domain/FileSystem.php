<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Symfony File System Wrapper.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class FileSystem
{
    /**
     * @throws \InvalidArgumentException
     */
    public function getRealPath(string $path): string
    {
        $result = realpath($path);

        if (false === $result) {
            throw new \InvalidArgumentException('The path you give is not a real one');
        }

        return $result;
    }

    public function getFileLines(string $path): array
    {
        $result = file($path);

        if (false === $result) {
            throw new \InvalidArgumentException('The path you give is not a real one or the file is not readable');
        }

        return $result;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getFileLine(string $path, int $line): string
    {
        $lines = $this->getFileLines($path);

        if (count($lines) < $line) {
            throw new \InvalidArgumentException(sprintf(
                'There is less than %d lines in the %s file',
                $line,
                $path
            ));
        }

        return $lines[$line];
    }

    public function updateLineInFile(string $path, int $line, string $content): void
    {
        $lines = $this->getFileLines($path);
        $lines[$line] = $content;

        $this->getFileSystem()->dumpFile($path, implode('', $lines));
    }

    public function getFileSystem(): SymfonyFilesystem
    {
        return new SymfonyFilesystem();
    }

    public function getFileContent(string $path): string
    {
        $result = file_get_contents($path);

        if (false === $result) {
            throw new \InvalidArgumentException(
                'The path you give is not a real one or the file is not readable'
            );
        }

        return $result;
    }

    public function extractArchive(string $archivePath, string $to): void
    {
        if (false === $this->getFileSystem()->isAbsolutePath($archivePath)) {
            throw new \InvalidArgumentException('archivePath should be an absolute path');
        }

        if (false === $this->getFileSystem()->isAbsolutePath($to)) {
            throw new \InvalidArgumentException('to should be an absolute path');
        }

        $archive = new \PharData($archivePath);

        $archive->extractTo($to, null, true);
    }
}
