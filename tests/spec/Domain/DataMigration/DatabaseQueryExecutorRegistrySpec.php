<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutorRegistry;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for DatabaseQueryExecutor.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseQueryExecutorRegistrySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DatabaseQueryExecutorRegistry::class);
    }

    public function it_gets_a_supportable_query_executor(
        DatabaseQueryExecutor $databaseQueryExecutor1,
        DatabaseQueryExecutor $databaseQueryExecutor2,
        PimConnection $sourcePimConnection,
        SourcePim $pim
    ) {
        $this->addDatabaseQueryExecutor($databaseQueryExecutor1);
        $this->addDatabaseQueryExecutor($databaseQueryExecutor2);

        $pim->getConnection()->willReturn($sourcePimConnection);

        $databaseQueryExecutor1->supports($sourcePimConnection)->willReturn(false);
        $databaseQueryExecutor2->supports($sourcePimConnection)->willReturn(true);


        $this->get($pim)->shouldReturn($databaseQueryExecutor2);
    }

    public function it_throws_an_exception_if_there_is_no_console_supporting_the_connection(
        DatabaseQueryExecutor $databaseQueryExecutor1,
        DatabaseQueryExecutor $databaseQueryExecutor2,
        PimConnection $pimConnection,
        SourcePim $pim
    ) {
        $this->addDatabaseQueryExecutor($databaseQueryExecutor1);
        $this->addDatabaseQueryExecutor($databaseQueryExecutor2);

        $pim->getConnection()->willReturn($pimConnection);

        $databaseQueryExecutor1->supports($pimConnection)->willReturn(false);
        $databaseQueryExecutor2->supports($pimConnection)->willReturn(false);

        $this
            ->shouldThrow(new \InvalidArgumentException('The connection is not supported by any database query executor'))
            ->during('get', [$pim]);
    }
}
