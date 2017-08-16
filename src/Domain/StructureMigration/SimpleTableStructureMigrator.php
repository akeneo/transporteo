<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\StructureMigration;

use Akeneo\PimMigration\Domain\DatabaseServices\NaiveMigrator;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\FilesMigration\StructureMigrationException;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * .
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SimpleTableStructureMigrator implements TableStructureMigrator
{
    /** @var NaiveMigrator */
    private $naiveMigrator;

    /** @var string */
    private $supportedTableName;

    public function __construct(NaiveMigrator $naiveMigrator, string $supportedTableName)
    {
        $this->naiveMigrator = $naiveMigrator;
        $this->supportedTableName = $supportedTableName;
    }

    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $this->naiveMigrator->migrate($sourcePim, $destinationPim, $this->supportedTableName);
        } catch (\Exception $exception) {
            throw new StructureMigrationException(
                sprintf('%s %s', $this->supportedTableName, $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
