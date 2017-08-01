<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DestinationPimDownload;

use Akeneo\PimMigration\Domain\PimDetection\AbstractPim;
use GitElephant\Repository;

/**
 * Destination Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPim extends AbstractPim
{
    /** @var string */
    protected $path;

    /** @var Repository */
    protected $repository;

    public function __construct(
        string $mysqlHost = 'mysql',
        int $mysqlPort = 3306,
        string $databaseName = 'akeneo_pim',
        string $databaseUser = 'akeneo_pim',
        string $databasePassword = 'akeneo_pim',
        bool $isEnterpriseEdition,
        ?string $enterpriseRepository,
        string $path
    ) {
        parent::__construct(
            $mysqlHost,
            $mysqlPort,
            $databaseName,
            $databaseUser,
            $databasePassword,
            $isEnterpriseEdition,
            $enterpriseRepository
        );

        $this->path = $path;
        $this->repository = new Repository($path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    protected static function getPimVersionAllowed(): string
    {
        return '2.0.';
    }
}
