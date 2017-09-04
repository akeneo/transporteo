<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimDownload;

use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use GitElephant\Repository;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Download a PIM from Git.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class GitDestinationPimDownloader implements DestinationPimDownloader
{
    private const PIM_COMMUNITY_STANDARD_REPOSITORY = 'git@github.com:akeneo/pim-community-standard.git';
    private const PIM_ENTERPRISE_STANDARD_REPOSITORY = 'git@github.com:akeneo/pim-enterprise-standard.git';

    public function download(SourcePim $pim, string $projectName): string
    {
        $fs = new Filesystem();

        $repositoryPath = sprintf(
            '%s%s%s%s%s%s%s%s%s%s%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            '..',
            DIRECTORY_SEPARATOR,
            'var',
            DIRECTORY_SEPARATOR,
            $projectName
        );

        $fs->mkdir($repositoryPath);

        $repositoryPath = realpath($repositoryPath);

        $repository = Repository::createFromRemote(
            $pim->isEnterpriseEdition() ? self::PIM_ENTERPRISE_STANDARD_REPOSITORY : self::PIM_COMMUNITY_STANDARD_REPOSITORY,
            $repositoryPath,
            null,
            $projectName
        );

        //TODO put the right version
        $repository->checkout('master');

        return $repositoryPath;
    }
}
