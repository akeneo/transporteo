<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\MigrationStep\s30_AccessVerification\AccessVerificator;
use Akeneo\PimMigration\Infrastructure\AccessVerification\SshAccessVerificator;
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
    ): AccessVerificator {
        $ssh = new SSH2($serverAccessInformation->getHost(), $serverAccessInformation->getPort());
        $rsa = new RSA();

        $rsa->load($serverAccessInformation->getSshKey()->getKey());

        return new SshAccessVerificator($ssh, $rsa, $serverAccessInformation);
    }
}
