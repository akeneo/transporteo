<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * A Console is place where we can execute Command (eg:remote or local or docker).
 * It executes command like a Terminal and give you the result.
 *
 * Console is considered as part of the domain as we DO need them to interact with the PIM environment, however, the execution is part of the infrastructure ({@see Akeneo\PimMigration\Infrastructure\Cli\DockerConsole})}
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface Console
{
    /**
     * @throws UnsuccessfulCommandException
     */
    public function execute(Command $command, Pim $pim): CommandResult;

    public function supports(PimConnection $connection): bool;
}
