<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

/**
 * Factory to create EnterpriseEditionVerificator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionVerificatorFactory
{
    public function createSshEnterpriseVerificator(
        ServerAccessInformation $serverAccessInformation
    ): EnterpriseEditionAccessVerificator {
        $ssh = new SSH2($serverAccessInformation->getHost(), $serverAccessInformation->getPort());
        $rsa = new RSA();

        $rsa->load($serverAccessInformation->getSshKey()->getKey());

        return new SshEnterpriseEditionAccessVerificator($ssh, $rsa, $serverAccessInformation);
    }
}
