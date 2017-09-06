<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

use Akeneo\PimMigration\Domain\Pim\AbstractPim;
use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Console is place where we can execute UnixCommand (eg:remote or local or docker).
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface Console
{
    /**
     * @throws UnsuccessfulCommandException
     */
    public function execute(UnixCommand $command, AbstractPim $pim, PimConnection $connection): UnixCommandResult;

    public function supports(PimConnection $connection): bool;
}
