<?php

declare(strict_types=1);


namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;


use Akeneo\PimMigration\Domain\AbstractFile;
use Akeneo\PimMigration\Domain\File;

/**
 * Class to hold SSH Key.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshKey extends AbstractFile implements File
{
    protected function loadContent(): array
    {
        return [file_get_contents($this->getPath())];
    }

    public static function getFileName(): string
    {
        return 'ssh_key';
    }
}
