<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\MysqlEscaper;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Class LocalMysqlEscaper.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class LocalMysqlEscaper implements MysqlEscaper
{
    /** @var LocalMySqlQueryExecutor */
    private $mysqlQueryExecutor;

    public function __construct(LocalMySqlQueryExecutor $mysqlQueryExecutor)
    {
        $this->mysqlQueryExecutor = $mysqlQueryExecutor;
    }

    /**
     * {@inheritdoc}
     */
    public function escape(string $stringToEscape, Pim $pim): string
    {
        $mysqlConnection = $this->mysqlQueryExecutor->getConnection($pim);

        return $mysqlConnection->quote($stringToEscape);
    }
}
