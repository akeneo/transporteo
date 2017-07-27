<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;

/**
 * Factory to create EnterpriseEditionVerificator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionVerificatorFactory
{
    public function createSshEnterpriseVerificator(SshKey $sshKey): EnterpriseEditionAccessVerificator
    {
        return new SshEnterpriseEditionAccessVerificator($sshKey);
    }
}
